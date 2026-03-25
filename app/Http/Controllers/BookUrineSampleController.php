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
     * Optimizado con paginación del lado del servidor
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search', '');

        $query = CharacteristicSample::query()
            ->join('samples', 'characteristic_samples.sample_id', '=', 'samples.id')
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->where('samples.type', '=', 'orina')
            ->whereNull('samples.deleted_at')
            ->whereNull('characteristic_samples.deleted_at')
            ->select([
                'characteristic_samples.id_characteristic_samples',
                'characteristic_samples.sample_id',
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
                'characteristic_samples.result_elisa',
                'characteristic_samples.result_inmuno',
                'characteristic_samples.tipo_analisis',
                'samples.id as sample_id_ref',
                'samples.external_id',
                'samples.internal_id',
                'samples.type',
                'samples.category',
                'samples.status as status_id',
                'samples.received_at',
                'samples.analyzed_at',
                'samples.sample_taken_at',
                'companies.name as company_name',
            ]);

        // Búsqueda global optimizada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('samples.external_id', 'like', "%{$search}%")
                    ->orWhere('samples.internal_id', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%")
                    ->orWhere('characteristic_samples.ph', 'like', "%{$search}%")
                    ->orWhere('characteristic_samples.densidad', 'like', "%{$search}%");
            });
        }

        $urineSamples = $query->orderBy('characteristic_samples.id_characteristic_samples', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('booksample/urine/Index', [
            'urineSamples' => $urineSamples->items(),
            'pagination' => [
                'current_page' => $urineSamples->currentPage(),
                'last_page' => $urineSamples->lastPage(),
                'per_page' => $urineSamples->perPage(),
                'total' => $urineSamples->total(),
                'from' => $urineSamples->firstItem(),
                'to' => $urineSamples->lastItem(),
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
     * Optimizado: Transacción única para ambas tablas
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
            'tipo_analisis' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'cantidad_droga' => ['nullable', 'integer'],
            'encargado_ingreso' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['nullable', 'date'],
            'sample_taken_at' => ['nullable', 'date'],
        ]);

        // Usar transacción para ambas actualizaciones
        \DB::transaction(function () use ($validated, $id) {
            $characteristic = CharacteristicSample::findOrFail($id);

            // Preparar datos para tabla samples
            $sampleData = [];
            $characteristicData = $validated;

            if (array_key_exists('internal_id', $validated)) {
                $sampleData['internal_id'] = $validated['internal_id'];
                unset($characteristicData['internal_id']);
            }

            if (array_key_exists('fecha_ingreso', $validated)) {
                $sampleData['analyzed_at'] = $validated['fecha_ingreso'];
            }

            if (array_key_exists('sample_taken_at', $validated)) {
                $sampleData['sample_taken_at'] = $validated['sample_taken_at'];
                unset($characteristicData['sample_taken_at']);
            }

            // Actualizar ambas tablas en una transacción
            if (! empty($sampleData)) {
                Sample::where('id', $characteristic->sample_id)->update($sampleData);
            }

            $characteristic->update($characteristicData);
        });

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
            'result_elisa' => ['nullable', 'string'],
            'result_inmuno' => ['nullable', 'string'],
        ]);

        $characteristic = CharacteristicSample::findOrFail($id);
        $characteristic->update($validated);

        return redirect()->route('bookurinesample.index')
            ->with('success', 'Resultados de la muestra actualizados correctamente.');
    }

    private function formatResult($value)
    {
        if (empty($value)) {
            return '-';
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $parts = [];
            foreach ($decoded as $key => $val) {
                $parts[] = "$key: $val";
            }

            return implode(' - ', $parts);
        }

        return $value;
    }

    /**
     * Export urine samples to Excel based on date range
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'tipo_filtro' => 'required|in:fecha,internal_id',
            'fecha_inicio' => 'required_if:tipo_filtro,fecha|date',
            'fecha_fin' => 'required_if:tipo_filtro,fecha|date|after_or_equal:fecha_inicio',
            'internal_id_inicio' => 'required_if:tipo_filtro,internal_id|string|max:255',
            'internal_id_fin' => 'required_if:tipo_filtro,internal_id|string|max:255',
        ]);

        $query = CharacteristicSample::query()
            ->join('samples', 'characteristic_samples.sample_id', '=', 'samples.id')
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->where('samples.type', '=', 'orina');

        if ($validated['tipo_filtro'] === 'internal_id') {
            $query->whereBetween('samples.internal_id', [
                $validated['internal_id_inicio'],
                $validated['internal_id_fin'],
            ]);
        } else {
            $query->whereBetween('samples.analyzed_at', [
                $validated['fecha_inicio'],
                $validated['fecha_fin'],
            ]);
        }

        $samples = $query
            ->select([
                'samples.internal_id',
                'samples.external_id',
                'samples.received_at',
                'samples.analyzed_at',
                'characteristic_samples.encargado_ingreso',
                'companies.name as company_name',
                'characteristic_samples.volumen',
                'characteristic_samples.ph',
                'characteristic_samples.densidad',
                'characteristic_samples.observaciones',
                'characteristic_samples.screening',
                'characteristic_samples.confirmacion',
                'characteristic_samples.result_gcms',
                'characteristic_samples.result_cobas',
            ])
            ->orderBy('samples.analyzed_at', 'asc')
            ->get();

        $lastCol = 'N';
        $rowsPerPage = 15;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);
        $sheet = $spreadsheet->getActiveSheet();

        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(1, 1);

        $margins = $sheet->getPageMargins();
        $margins->setTop(0.4);
        $margins->setBottom(0.4);
        $margins->setLeft(0.3);
        $margins->setRight(0.3);
        $margins->setHeader(0.2);
        $margins->setFooter(0.2);

        $sheet->getHeaderFooter()->setOddFooter('&CPágina &P de &N');

        $headers = [
            'N°Codigo interno',
            'N°Codigo externo',
            'Fecha y hora recepción',
            'Fecha y hora ingreso',
            'Persona que ingresa',
            'Procedencia/sello y/o código',
            'Volumen',
            'pH',
            'Densidad',
            'Observaciones',
            'Screening',
            'Confirmación',
            'Resultado GC/MS',
            'Resultado COBAS',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $row = 2;
        foreach ($samples as $sample) {
            $receivedAt = $sample->received_at
                ? \Carbon\Carbon::parse($sample->received_at)->format('d-m-Y H:i')
                : '-';
            $analyzedAt = $sample->analyzed_at
                ? \Carbon\Carbon::parse($sample->analyzed_at)->format('d-m-Y H:i')
                : '-';

            $sheet->fromArray([
                $sample->internal_id ?: '-',
                $sample->external_id ?: '-',
                null,
                null,
                $sample->encargado_ingreso ?: '-',
                $sample->company_name ?: '-',
                $sample->volumen ?: '-',
                $sample->ph ?: '-',
                $sample->densidad ?: '-',
                $sample->observaciones ?: '-',
                $sample->screening ?: '-',
                $sample->confirmacion ?: '-',
                $this->formatResult($sample->result_gcms),
                $this->formatResult($sample->result_cobas),
            ], null, 'A'.$row);

            $sheet->setCellValueExplicit("C{$row}", $receivedAt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", $analyzedAt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $row++;
        }

        $lastRow = $row - 1;

        $dataStyle = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        for ($breakRow = 2 + $rowsPerPage; $breakRow <= $lastRow; $breakRow += $rowsPerPage) {
            $sheet->setBreak("A{$breakRow}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        }

        $filename = 'muestras_orina_'.date('Y-m-d_His').'.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
