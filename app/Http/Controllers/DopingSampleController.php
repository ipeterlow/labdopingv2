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
     */
    public function index()
    {
        $samples = Sample::join('companies', 'samples.company_id', '=', 'companies.id')
            ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->select('samples.id', 'samples.external_id', 'samples.internal_id', 'samples.category', 'sample_status.name as status_name', 'samples.type', 'samples.sent_at', 'samples.received_at', 'samples.analyzed_at', 'samples.sample_taken_at', 'samples.results_at', 'companies.name as company_name')
            ->orderBy('samples.id', 'desc')
            ->get();

        return Inertia::render('dopingsample/Index', [
            'sample' => $samples,
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
     */
    public function store(Request $request)
    {
        // 1) Validación (alineada al Create.vue)
        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],

            'sent_at' => ['required', 'date'],               // yyyy-MM-dd
            'received_at' => ['required', 'date'],               // yyyy-MM-dd
            'received_at_hour' => ['required', 'date_format:H:i'],    // HH:mm

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

        // Obtener el reception_id más alto de todos los registros y sumar 1
        // Usamos CAST para asegurar comparación numérica (por si es string en DB)
        $maxReceptionId = DB::table('samples')
            ->whereNotNull('reception_id')
            ->selectRaw('MAX(CAST(reception_id AS UNSIGNED)) as max_id')
            ->value('max_id');
        $keygen = $maxReceptionId ? (int) $maxReceptionId + 1 : 1;
        // Campos comunes para todas las filas de samples
        $commons = [
            'company_id' => $data['company_id'],
            'description' => $data['description'] ?? null,
            'shipping_type' => $shipping,
            'sent_at' => $data['sent_at'],
            'received_at' => $receivedAt,
            'user_id' => optional($request->user())->id, // si usas auth
            // Puedes setear defaults si corresponde:
            'status' => '1',
            'reception_id' => $keygen,
        ];

        // 3) Crear una fila en "samples" por cada item ingresado
        foreach ($data['samples'] as $s) {
            // Si la categoría es "A-B", crear dos registros (uno para A y otro para B)
            if ($s['category'] === 'A-B') {
                // Crear muestra categoría A
                Sample::create(array_merge($commons, [
                    'external_id' => $s['external'],
                    'type' => $s['type'],
                    'category' => 'A',
                ]));

                // Crear muestra categoría B
                Sample::create(array_merge($commons, [
                    'external_id' => $s['external'],
                    'type' => $s['type'],
                    'category' => 'B',
                ]));
            } else {
                // Crear una sola muestra con la categoría especificada
                Sample::create(array_merge($commons, [
                    'external_id' => $s['external'],
                    'type' => $s['type'],
                    'category' => $s['category'],
                ]));
            }
        }

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
     */
    public function edit(string $id)
    {
        // 1. Obtener la muestra en la que se hizo clic
        $clickedSample = Sample::findOrFail($id);
        $receptionId = $clickedSample->reception_id;
        $mainSampleData = null;

        // 2. Buscar datos comunes (Fecha, Hora, Envío)
        if ($receptionId) {
            // Si el ID NO es null, busca la "muestra principal" de ese grupo
            $mainSampleData = Sample::where('reception_id', $receptionId)
                ->whereNotNull('received_at') // Busca una que SÍ tenga datos
                ->first();
        }

        // Fallback: Si no se encontró (o si $receptionId era null),
        // usamos la muestra en la que se hizo clic (aunque venga con nulls).
        if (! $mainSampleData) {
            $mainSampleData = $clickedSample;
        }

        // 3. Cargar el GRUPO de muestras (el listado)
        $sampleGroup = null;
        if ($receptionId) {
            // Si el ID NO es null, carga el grupo completo
            $sampleGroup = Sample::where('reception_id', $receptionId)
                //  ¡¡¡ARREGLO DE 'external' AQUÍ!!!
                ->select('id', 'external_id as external', 'type', 'category')
                ->orderBy('id')
                ->get();
        } else {
            // ¡¡¡ARREGLO DE "12 MUESTRAS" AQUÍ!!!
            // Si el ID ES null, el "grupo" es solo esta muestra rota
            $sampleGroup = collect([
                [
                    'id' => $clickedSample->id,
                    'external' => $clickedSample->external_id, // Renombramos aquí también
                    'type' => $clickedSample->type,
                    'category' => $clickedSample->category,
                ],
            ]);
        }

        // 4. Cargar Empresas
        $companies = Company::orderBy('name')->get(['id', 'name']);

        // 5. Pre-procesar datos para el formulario (usando $mainSampleData)
        $standardShipping = ['Chilexpress', 'Correos de Chile', 'Soserval', 'Pullman', 'Speed Cargo', 'Starken', 'Chibra'];

        // Procesar fecha y hora de recepción
        $receivedAtDate = null;
        $receivedAtHour = $mainSampleData->received_at_hour;

        if ($mainSampleData->received_at) {
            if (is_string($mainSampleData->received_at)) {
                // Si es string, extraer fecha y hora
                $receivedAtDate = date('Y-m-d', strtotime($mainSampleData->received_at));
                if (! $receivedAtHour) {
                    $receivedAtHour = date('H:i', strtotime($mainSampleData->received_at));
                }
            } else {
                // Si es Carbon, usar sus métodos
                $receivedAtDate = $mainSampleData->received_at->format('Y-m-d');
                if (! $receivedAtHour) {
                    $receivedAtHour = $mainSampleData->received_at->format('H:i');
                }
            }
        }

        // Usamos $mainSampleData para rellenar los datos comunes
        $formSample = [
            'id' => $mainSampleData->id,
            'reception_id' => $mainSampleData->reception_id,
            'company_id' => $mainSampleData->company_id,
            'sent_at' => $mainSampleData->sent_at ?
                (is_string($mainSampleData->sent_at) ? date('Y-m-d', strtotime($mainSampleData->sent_at)) : $mainSampleData->sent_at->format('Y-m-d')) : null,
            'received_at' => $receivedAtDate,
            'received_at_hour' => $receivedAtHour,
            'description' => $mainSampleData->description,
        ];

        // Lógica para separar 'otros' (usando $mainSampleData)
        if (in_array($mainSampleData->shipping_type, $standardShipping)) {
            $formSample['shipping_type'] = $mainSampleData->shipping_type;
            $formSample['custom_shipping_type'] = '';
        } else {
            $formSample['shipping_type'] = 'otros';
            $formSample['custom_shipping_type'] = $mainSampleData->shipping_type;
        }

        // 6. Renderizar
        return Inertia::render('dopingsample/Edit', [ // Asegúrate que la ruta sea correcta
            'companies' => $companies,
            'sample' => $formSample,
            'sampleGroup' => $sampleGroup, // <-- Ahora enviará el grupo correcto
        ]);
    }

    /**
     * Actualiza un grupo de muestras.
     * La ruta debe ser: Route::put('/dopingsample/{reception_id}', ...)->name('dopingsample.update');
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
            'samples' => ['required', 'array', 'min:1'], // Debe quedar al menos una muestra
            'samples.*.id' => ['required', 'integer', 'exists:samples,id'],
            'samples.*.external' => ['nullable', 'string', 'max:255'],
            'samples.*.type' => ['required', Rule::in(['orina', 'pelo', 'saliva', 'suero'])],
            'samples.*.category' => ['required', Rule::in(['A', 'B'])],
        ]);

        // 2. Procesar el tipo de envío
        $shippingType = $validated['shipping_type'];
        if ($shippingType === 'otros' && ! empty($validated['custom_shipping_type'])) {
            $shippingType = $validated['custom_shipping_type'];
        }

        // 3. Combinar fecha y hora en un timestamp completo
        $receivedAtTimestamp = $validated['received_at'].' '.$validated['received_at_hour'].':00';

        // 4. Usar una transacción para asegurar la integridad de los datos
        DB::transaction(function () use ($validated, $reception_id, $shippingType, $receivedAtTimestamp) {

            $requestSamples = $validated['samples'];
            $requestIds = collect($requestSamples)->pluck('id')->all();

            // 5. Eliminar muestras que ya no están en la lista
            // Se borra cualquier muestra de este grupo que NO haya venido en el formulario
            Sample::where('reception_id', $reception_id)
                ->whereNotIn('id', $requestIds)
                ->delete();

            // 6. Actualizar las muestras restantes
            foreach ($requestSamples as $sampleData) {
                Sample::where('id', $sampleData['id'])
                    ->where('reception_id', $reception_id) // Doble chequeo de seguridad
                    ->update([
                        // Datos comunes (se actualizan en todas las muestras del grupo)
                        'company_id' => $validated['company_id'],
                        'sent_at' => $validated['sent_at'],
                        'received_at' => $receivedAtTimestamp,
                        'description' => $validated['description'],
                        'shipping_type' => $shippingType,

                        // Datos específicos de la muestra
                        'external_id' => $sampleData['external'],
                        'type' => $sampleData['type'],
                        'category' => $sampleData['category'],
                    ]);
            }
        });

        // 6. Redirigir de vuelta a la página de edición (necesita un ID de muestra, no de recepción)
        $firstSampleId = $validated['samples'][0]['id'];

        return to_route('dopingsample.edit', $firstSampleId);
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
