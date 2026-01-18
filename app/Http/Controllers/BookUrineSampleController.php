<?php

namespace App\Http\Controllers;

use App\Models\CharacteristicSample;
use App\Models\Sample;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookUrineSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $urineSamples = CharacteristicSample::query()
            ->join('samples', 'characteristic_samples.sample_id', '=', 'samples.id')
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->where('samples.type', '=', 'orina')
            ->select([
                'characteristic_samples.id_characteristic_samples',
                'samples.external_id',
                'samples.internal_id',
                'samples.type',
                'samples.category',
                'samples.status as status_id',
                'samples.received_at',
                'samples.analyzed_at',
                'characteristic_samples.*',
                'companies.name as company_name',
            ])
            ->orderBy('characteristic_samples.id_characteristic_samples', 'desc')
            ->get();

        return Inertia::render('booksample/urine/Index', [
            'urineSamples' => $urineSamples,
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
            'ph' => ['nullable', 'string', 'max:50'],
            'densidad' => ['nullable', 'string', 'max:50'],
            'volumen' => ['nullable', 'string', 'max:50'],
            'screening' => ['nullable', 'string', 'max:255'],
            'confirmacion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'cantidad_droga' => ['nullable', 'integer'],
            'encargado_ingreso' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
        ]);

        \Log::info('📥 Datos validados recibidos:', $validated);
        \Log::info('📥 Screening: '.($validated['screening'] ?? 'null'));
        \Log::info('📥 Confirmacion: '.($validated['confirmacion'] ?? 'null'));

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
        if (! empty($sampleData)) {
            $characteristic->sample()->update($sampleData);
        }

        // Actualizar características
        $characteristic->update($validated);

        return redirect()->route('bookurinesample.index')
            ->with('success', 'Características de orina actualizadas correctamente.');
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

    /**
     * Update the results (GC/MS and COBAS) of the specified sample.
     */
    public function updateResults(Request $request, string $id)
    {
        $validated = $request->validate([
            'result_gcms' => ['nullable', 'string'],
            'result_cobas' => ['nullable', 'string'],
        ]);

        $characteristic = CharacteristicSample::findOrFail($id);
        $characteristic->update($validated);

        return redirect()->route('bookurinesample.index')
            ->with('success', 'Resultados de la muestra actualizados correctamente.');
    }

    /**
     * Export urine samples to Excel based on date range
     */
    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $samples = CharacteristicSample::query()
            ->join('samples', 'characteristic_samples.sample_id', '=', 'samples.id')
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->where('samples.type', '=', 'orina')
            ->whereBetween('samples.analyzed_at', [$request->fecha_inicio, $request->fecha_fin])
            ->select([
                'samples.external_id',
                'samples.internal_id',
                'samples.category',
                'companies.name as company_name',
                'samples.received_at',
                'samples.analyzed_at',
                'characteristic_samples.ph',
                'characteristic_samples.densidad',
                'characteristic_samples.volumen',
                'characteristic_samples.screening',
                'characteristic_samples.confirmacion',
                'characteristic_samples.observaciones',
                'characteristic_samples.cantidad_droga',
                'characteristic_samples.encargado_ingreso',
                'characteristic_samples.fecha_ingreso',
                'characteristic_samples.result_gcms',
                'characteristic_samples.result_cobas',
            ])
            ->orderBy('samples.analyzed_at', 'asc')
            ->get();

        // Crear spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'Nº Externo',
            'Nº Interno',
            'Tipo',
            'Empresa',
            'Fecha Recepción',
            'Fecha Análisis',
            'pH',
            'Densidad',
            'Volumen',
            'Screening',
            'Confirmación',
            'Observaciones',
            'Cantidad de Droga',
            'Encargado de Ingreso',
            'Fecha de Ingreso',
            'Resultado GC/MS',
            'Resultado COBAS',
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Estilo para headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ];
        $sheet->getStyle('A1:Q1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        foreach ($samples as $sample) {
            $sheet->fromArray([
                $sample->external_id,
                $sample->internal_id ?: '-',
                $sample->category ?: '-',
                $sample->company_name,
                $sample->received_at ?: '-',
                $sample->analyzed_at ?: '-',
                $sample->ph ?: '-',
                $sample->densidad ?: '-',
                $sample->volumen ?: '-',
                $sample->screening ?: '-',
                $sample->confirmacion ?: '-',
                $sample->observaciones ?: '-',
                $sample->cantidad_droga ?: '-',
                $sample->encargado_ingreso ?: '-',
                $sample->fecha_ingreso ?: '-',
                $sample->result_gcms ?: '-',
                $sample->result_cobas ?: '-',
            ], null, 'A' . $row);
            $row++;
        }

        // Auto-ajustar columnas
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $filename = 'muestras_orina_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Enviar headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}

