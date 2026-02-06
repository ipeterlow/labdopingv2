<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Sample;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DopingSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Optimizado con paginación del lado del servidor
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search', '');

        $query = Sample::query()
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->select(
                'samples.id',
                'samples.external_id',
                'samples.internal_id',
                'samples.category',
                'sample_status.name as status_name',
                'samples.type',
                'samples.sent_at',
                'samples.received_at',
                'samples.analyzed_at',
                'samples.sample_taken_at',
                'samples.results_at',
                'companies.name as company_name'
            )
            ->whereNull('samples.deleted_at');

        // Búsqueda global optimizada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('samples.external_id', 'like', "%{$search}%")
                  ->orWhere('samples.internal_id', 'like', "%{$search}%")
                  ->orWhere('companies.name', 'like', "%{$search}%")
                  ->orWhere('sample_status.name', 'like', "%{$search}%");
            });
        }

        $samples = $query->orderBy('samples.id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('dopingsample/Index', [
            'sample' => $samples->items(),
            'pagination' => [
                'current_page' => $samples->currentPage(),
                'last_page' => $samples->lastPage(),
                'per_page' => $samples->perPage(),
                'total' => $samples->total(),
                'from' => $samples->firstItem(),
                'to' => $samples->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Traemos las companies altiro
        $companies = Company::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('dopingsample/Create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Optimizado: Inserción masiva con transacción
     */
    public function store(Request $request)
    {
        // 1) Validación (alineada al Create.vue)
        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'sent_at' => ['required', 'date'],
            'received_at' => ['required', 'date'],
            'received_at_hour' => ['required', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:2000'],
            'shipping_type' => ['required', Rule::in([
                'Chilexpress', 'Correos de Chile', 'Soserval', 'Pullman',
                'Speed Cargo', 'Starken', 'Chibra', 'otros',
            ])],
            'custom_shipping_type' => ['nullable', 'required_if:shipping_type,otros', 'string', 'max:255'],
            'samples' => ['required', 'array', 'min:1'],
            'samples.*.external' => ['required', 'string', 'max:255'],
            'samples.*.type' => ['required', Rule::in(['orina', 'pelo', 'saliva', 'suero'])],
            'samples.*.category' => ['required', Rule::in(['A', 'B', 'A-B'])],
        ]);

        // 2) Normalización de campos
        $shipping = $data['shipping_type'] === 'otros'
            ? $data['custom_shipping_type']
            : $data['shipping_type'];

        $receivedAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $data['received_at'].' '.$data['received_at_hour']
        );

        // 3) Usar transacción para mejorar rendimiento y garantizar integridad
        DB::transaction(function () use ($data, $shipping, $receivedAt, $request) {
            // Obtener el reception_id más alto (lock for update para evitar duplicados)
            $maxReceptionId = DB::table('samples')
                ->whereNotNull('reception_id')
                ->lockForUpdate()
                ->max(DB::raw('CAST(reception_id AS UNSIGNED)'));
            
            $keygen = $maxReceptionId ? (int) $maxReceptionId + 1 : 1;
            $now = now();
            
            // Preparar todos los registros para inserción masiva
            $samplesToInsert = [];
            
            foreach ($data['samples'] as $s) {
                $baseRecord = [
                    'company_id' => $data['company_id'],
                    'description' => $data['description'] ?? null,
                    'shipping_type' => $shipping,
                    'sent_at' => $data['sent_at'],
                    'received_at' => $receivedAt,
                    'user_id' => $request->user()?->id,
                    'status' => '1',
                    'reception_id' => $keygen,
                    'external_id' => $s['external'],
                    'type' => $s['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($s['category'] === 'A-B') {
                    // Crear dos registros
                    $samplesToInsert[] = array_merge($baseRecord, ['category' => 'A']);
                    $samplesToInsert[] = array_merge($baseRecord, ['category' => 'B']);
                } else {
                    $samplesToInsert[] = array_merge($baseRecord, ['category' => $s['category']]);
                }
            }

            // Inserción masiva (mucho más rápido que múltiples insert individuales)
            Sample::insert($samplesToInsert);
        });

        // 4) Respuesta
        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return to_route('dopingsample.index')
            ->with('success', 'Muestras creadas correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sample = Sample::join('companies', 'samples.company_id', '=', 'companies.id')
            ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->select('samples.*', 'companies.name as company_name', 'sample_status.name as status_name')
            ->where('samples.id', $id)
            ->firstOrFail();

        return Inertia::render('dopingsample/Show', [
            'sample' => $sample,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Optimizado: Reducción de queries
     */
    public function edit(string $id)
    {
        // 1. Obtener la muestra en la que se hizo clic
        $clickedSample = Sample::findOrFail($id);
        $receptionId = $clickedSample->reception_id;

        // 2. Si hay reception_id, cargar todo el grupo en una sola query
        $sampleGroup = collect();
        $mainSampleData = $clickedSample;

        if ($receptionId) {
            // Una sola query para obtener todo el grupo
            $sampleGroup = Sample::where('reception_id', $receptionId)
                ->select('id', 'external_id as external', 'type', 'category', 'received_at', 'company_id', 'sent_at', 'description', 'shipping_type')
                ->orderBy('id')
                ->get();

            // Usar la primera con received_at como principal, o la primera del grupo
            $mainSampleData = $sampleGroup->firstWhere('received_at', '!=', null) 
                ?? $sampleGroup->first() 
                ?? $clickedSample;
        } else {
            $sampleGroup = collect([
                [
                    'id' => $clickedSample->id,
                    'external' => $clickedSample->external_id,
                    'type' => $clickedSample->type,
                    'category' => $clickedSample->category,
                ],
            ]);
        }

        // 3. Cargar empresas (considerar cachear esto)
        $companies = Company::select('id', 'name')->orderBy('name')->get();

        // 4. Pre-procesar datos para el formulario
        $standardShipping = ['Chilexpress', 'Correos de Chile', 'Soserval', 'Pullman', 'Speed Cargo', 'Starken', 'Chibra'];

        // Procesar fecha y hora de recepción
        $receivedAtDate = null;
        $receivedAtHour = $mainSampleData->received_at_hour ?? null;

        if ($mainSampleData->received_at) {
            $receivedAt = is_string($mainSampleData->received_at) 
                ? Carbon::parse($mainSampleData->received_at) 
                : $mainSampleData->received_at;
            
            $receivedAtDate = $receivedAt->format('Y-m-d');
            $receivedAtHour = $receivedAtHour ?? $receivedAt->format('H:i');
        }

        $sentAt = $mainSampleData->sent_at;
        if ($sentAt) {
            $sentAt = is_string($sentAt) ? date('Y-m-d', strtotime($sentAt)) : $sentAt->format('Y-m-d');
        }

        // Preparar datos del formulario
        $formSample = [
            'id' => $mainSampleData->id,
            'reception_id' => $receptionId ?? $clickedSample->reception_id,
            'company_id' => $mainSampleData->company_id,
            'sent_at' => $sentAt,
            'received_at' => $receivedAtDate,
            'received_at_hour' => $receivedAtHour,
            'description' => $mainSampleData->description,
            'shipping_type' => in_array($mainSampleData->shipping_type, $standardShipping) 
                ? $mainSampleData->shipping_type 
                : 'otros',
            'custom_shipping_type' => in_array($mainSampleData->shipping_type, $standardShipping) 
                ? '' 
                : $mainSampleData->shipping_type,
        ];

        return Inertia::render('dopingsample/Edit', [
            'companies' => $companies,
            'sample' => $formSample,
            'sampleGroup' => $sampleGroup,
        ]);
    }

    /**
     * Actualiza un grupo de muestras.
     * Optimizado: Update masivo de datos comunes + updates individuales solo para datos específicos
     */
    public function update(Request $request, $reception_id)
    {
        // 1. Validación de los datos
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'sent_at' => ['required', 'date'],
            'received_at' => ['required', 'date'],
            'received_at_hour' => ['required'],
            'description' => ['nullable', 'string'],
            'shipping_type' => ['required', 'string'],
            'custom_shipping_type' => ['nullable', 'string', 'max:255'],
            'samples' => ['required', 'array', 'min:1'],
            'samples.*.id' => ['required', 'integer', 'exists:samples,id'],
            'samples.*.external' => ['nullable', 'string', 'max:255'],
            'samples.*.type' => ['required', Rule::in(['orina', 'pelo', 'saliva', 'suero'])],
            'samples.*.category' => ['required', Rule::in(['A', 'B'])],
        ]);

        // 2. Procesar el tipo de envío
        $shippingType = $validated['shipping_type'] === 'otros' && !empty($validated['custom_shipping_type'])
            ? $validated['custom_shipping_type']
            : $validated['shipping_type'];

        // 3. Combinar fecha y hora
        $receivedAtTimestamp = $validated['received_at'] . ' ' . $validated['received_at_hour'] . ':00';

        $requestSamples = $validated['samples'];
        $requestIds = collect($requestSamples)->pluck('id')->all();

        // 4. Usar transacción para integridad
        DB::transaction(function () use ($validated, $reception_id, $shippingType, $receivedAtTimestamp, $requestSamples, $requestIds) {
            // Eliminar muestras que ya no están (una sola query)
            Sample::where('reception_id', $reception_id)
                ->whereNotIn('id', $requestIds)
                ->delete();

            // Update masivo de datos COMUNES (una sola query para todos)
            Sample::where('reception_id', $reception_id)
                ->whereIn('id', $requestIds)
                ->update([
                    'company_id' => $validated['company_id'],
                    'sent_at' => $validated['sent_at'],
                    'received_at' => $receivedAtTimestamp,
                    'description' => $validated['description'],
                    'shipping_type' => $shippingType,
                ]);

            // Updates individuales SOLO para datos específicos (external_id, type, category)
            // Usando CASE WHEN para hacer un solo query
            if (count($requestSamples) > 0) {
                $cases = ['external_id' => [], 'type' => [], 'category' => []];
                $ids = [];
                
                foreach ($requestSamples as $sample) {
                    $id = $sample['id'];
                    $ids[] = $id;
                    $cases['external_id'][] = "WHEN id = {$id} THEN " . DB::getPdo()->quote($sample['external'] ?? '');
                    $cases['type'][] = "WHEN id = {$id} THEN " . DB::getPdo()->quote($sample['type']);
                    $cases['category'][] = "WHEN id = {$id} THEN " . DB::getPdo()->quote($sample['category']);
                }

                $idsStr = implode(',', $ids);
                $sql = "UPDATE samples SET 
                    external_id = CASE " . implode(' ', $cases['external_id']) . " END,
                    type = CASE " . implode(' ', $cases['type']) . " END,
                    category = CASE " . implode(' ', $cases['category']) . " END
                    WHERE id IN ({$idsStr}) AND reception_id = ?";
                
                DB::statement($sql, [$reception_id]);
            }
        });

        return to_route('dopingsample.edit', $validated['samples'][0]['id']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        Sample::destroy($id);

        return redirect()->route('dopingsample.index')->with('success', 'Muestra eliminada exitosamente.');

    }

    public function download(Sample $sample)
    {
        // Trae todas las muestras relacionadas, sólo con columnas necesarias
        $samples = Sample::query()
            ->where('reception_id', $sample->reception_id)
            ->get(['id', 'external_id', 'category', 'type', 'reception_id']);

        $types = ['orina', 'pelo', 'saliva', 'suero'];

        // Inicializa conteo
        $stats = collect(['A', 'B'])
            ->mapWithKeys(fn ($cat) => [$cat => array_fill_keys($types, 0)])
            ->toArray();

        // Contar muestras por tipo y categoría
        foreach ($samples as $s) {
            $category = strtoupper($s->category ?? '');
            $type = Str::lower($s->type ?? '');

            $targets = match ($category) {
                'A' => ['A'],
                'B' => ['B'],
                default => ['A', 'B'],
            };

            foreach ($targets as $cat) {
                if (array_key_exists($type, $stats[$cat])) {
                    $stats[$cat][$type]++;
                }
            }
        }

        $total = collect($stats)->flatten()->sum();

        // Agrupar por external_id para evitar duplicados (solo mostrar una vez cada código)
        $uniqueSamples = $samples->unique('external_id')->values();

        // Localiza fechas
        Carbon::setLocale('es');
        $receivedAt = Carbon::parse($sample->received_at);

        // Obtiene nombres sin cargar modelos completos
        $userName = User::query()->find($sample->user_id)?->name ?? '—';
        $companyName = Company::query()->find($sample->company_id)?->name ?? '—';

        $data = [
            'id' => $sample->reception_id,
            'external_id' => $sample->external_id,
            'received_at' => $receivedAt->isoFormat('dddd D [de] MMMM YYYY'),
            'received_at_hour' => $receivedAt->format('H:i'),
            'shipping_type' => $sample->shipping_type ?? '—',
            'description' => $sample->description ?? '',
            'user_register_sample' => $userName,
            'company' => $companyName,
            'orinaA' => $stats['A']['orina'],
            'orinaB' => $stats['B']['orina'],
            'peloA' => $stats['A']['pelo'],
            'peloB' => $stats['B']['pelo'],
            'salivaA' => $stats['A']['saliva'],
            'salivaB' => $stats['B']['saliva'],
            'sueroA' => $stats['A']['suero'],
            'sueroB' => $stats['B']['suero'],
            'samples' => $total,
            'samples_list' => $uniqueSamples,
        ];

        $pdf = Pdf::loadView('pdfs.recepcion_muestras', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->download("Recepcion-Muestras-{$sample->reception_id}.pdf");
    }

    /**
     * Update the status of the specified sample.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|integer|min:1|max:5',
        ]);

        $sample = Sample::findOrFail($id);
        $sample->status = $validated['status'];
        $sample->save();

        return redirect()->back()->with('success', 'Estado de muestra actualizado correctamente');
    }
}
