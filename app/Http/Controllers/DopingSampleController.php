<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Company;
use Illuminate\Validation\Rule;
use Carbon\CarbonImmutable;
use App\Models\Sample;


class DopingSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $samples = Sample::join('companies', 'samples.company_id', '=', 'companies.id')
        ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->select('samples.id','samples.external_id', 'samples.internal_id', 'samples.category','sample_status.name as status_name', 'samples.type','samples.sent_at','samples.received_at','samples.analyzed_at','samples.sample_taken_at','samples.results_at','companies.name as company_name')
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
            'company_id'           => ['required', 'integer', 'exists:companies,id'],

            'sent_at'              => ['required', 'date'],               // yyyy-MM-dd
            'received_at'          => ['required', 'date'],               // yyyy-MM-dd
            'received_at_hour'     => ['required', 'date_format:H:i'],    // HH:mm

            'description'          => ['nullable', 'string', 'max:2000'],

            'shipping_type'        => ['required', Rule::in([
                'Chilexpress', 'Correos de Chile', 'Soserval', 'Pullman',
                'Speed Cargo', 'Starken', 'Chibra', 'otros'
            ])],
            'custom_shipping_type' => ['nullable', 'required_if:shipping_type,otros', 'string', 'max:255'],

            'samples'              => ['required', 'array', 'min:1'],
            'samples.*.external'   => ['required', 'string', 'max:255'],
            'samples.*.type'       => ['required', Rule::in(['orina','pelo','saliva','suero'])],
            'samples.*.category'   => ['required', Rule::in(['A','B'])],
        ]);

        // 2) Normalización de campos
        $shipping = $data['shipping_type'] === 'otros'
            ? $data['custom_shipping_type']
            : $data['shipping_type'];

        $receivedAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $data['received_at'].' '.$data['received_at_hour']
        );

        // Campos comunes para todas las filas de samples
        $commons = [
            'company_id'    => $data['company_id'],
            'description'   => $data['description'] ?? null,
            'shipping_type' => $shipping,
            'sent_at'       => $data['sent_at'],
            'received_at'   => $receivedAt,
            'user_id'       => optional($request->user())->id, // si usas auth
            // Puedes setear defaults si corresponde:
            // 'status'        => 'pendiente',
        ];

        // 3) Crear una fila en "samples" por cada item ingresado
        foreach ($data['samples'] as $s) {
            Sample::create(array_merge($commons, [
                'external_id' => $s['external'],
                'type'        => $s['type'],
                'category'    => $s['category'],
                // estos campos existen en el modelo pero no vienen del form:
                // 'internal_id'   => null,
                // 'analyzed_at'   => null,
                // 'sample_taken_at' => null,
                // 'results_at'    => null,
                // 'document'      => null,
                // 'reception_id'  => null,
            ]));
        }

        // 4) Respuesta
        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()
            ->route('dopingsample.create')
            ->with('success', 'Muestras creadas correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
