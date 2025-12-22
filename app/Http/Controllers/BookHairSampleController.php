<?php

namespace App\Http\Controllers;

use App\Models\CharacteristicSample;
use App\Models\Sample;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookHairSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hairSamples = CharacteristicSample::query()
            ->join('samples', 'characteristic_samples.sample_id', '=', 'samples.id')
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->where('samples.type', '=', 'pelo')
            ->select([
                'characteristic_samples.id_characteristic_samples',
                'samples.external_id',
                'samples.internal_id',
                'samples.type',
                'samples.status as status_id',
                'samples.received_at',
                'samples.analyzed_at',
                'characteristic_samples.*',
                'companies.name as company_name',
            ])
            ->orderBy('characteristic_samples.id_characteristic_samples', 'desc')
            ->get();

        return Inertia::render('booksample/hair/Index', [
            'hairSamples' => $hairSamples,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Nota: Este método no se utiliza porque el registro en characteristic_samples
     * se crea automáticamente al crear la muestra. Solo se actualiza mediante update().
     */
    public function store(Request $request)
    {
        abort(404, 'Método no disponible. Use update() para actualizar características.');
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
        $validated = $request->validate([
            'internal_id' => ['nullable', 'string', 'max:255'],
            'largo' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'screening' => ['nullable', 'string', 'max:255'],
            'confirmacion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'cantidad_droga' => ['nullable', 'integer'],
            'encargado_ingreso' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
        ]);

        $characteristic = CharacteristicSample::findOrFail($id);

        // Preparar datos para actualizar en la tabla samples
        $sampleData = [];
        
        if (isset($validated['internal_id'])) {
            $sampleData['internal_id'] = $validated['internal_id'];
            unset($validated['internal_id']);
        }

        if (isset($validated['fecha_ingreso'])) {
            $sampleData['analyzed_at'] = $validated['fecha_ingreso'];
        }

        // Actualizar tabla samples
        if (!empty($sampleData)) {
            $characteristic->sample()->update($sampleData);
        }

        // Actualizar características
        $characteristic->update($validated);

        return redirect()->route('bookhairsample.index')
            ->with('success', 'Características de pelo actualizadas correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
