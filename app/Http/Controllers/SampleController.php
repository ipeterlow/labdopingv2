<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTeamId = auth()->user()->current_team_id;

        $query = Sample::query()
            ->join('companies', 'samples.company_id', '=', 'companies.id')
            ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->leftJoin('documents', function ($join) {
                $join->on('samples.id', '=', 'documents.sample_id')
                    ->where('documents.type_document', '=', 'informe');
            })
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
                'companies.name as company_name',
                'documents.created_at as informe_created_at'
            )
            // Calcular tiempos directamente en SQL (mucho más rápido)
            ->selectRaw('DATEDIFF(samples.received_at, samples.sent_at) as tiempo_recepcion')
            // Días hábiles (sin sábados ni domingos) para tiempo de respuesta
            ->selectRaw('
                CASE 
                    WHEN documents.created_at IS NULL OR samples.received_at IS NULL THEN NULL
                    ELSE (
                        DATEDIFF(documents.created_at, samples.received_at) - 
                        FLOOR((DATEDIFF(documents.created_at, samples.received_at) + WEEKDAY(samples.received_at) + 2) / 7) -
                        FLOOR((DATEDIFF(documents.created_at, samples.received_at) + WEEKDAY(samples.received_at) + 1) / 7)
                    )
                END as tiempo_respuesta
            ')
            ->orderBy('samples.id', 'desc');

        // Filtrar por team si corresponde
        if ($currentTeamId !== null && $currentTeamId !== '') {
            $query->where('samples.company_id', $currentTeamId);
        }

        $samples = $query->get();

        return Inertia::render('sample/Index', [
            'sample' => $samples,
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
     */
    public function store(Request $request)
    {
        //
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

        $documents = Document::where('sample_id', $sample->id)->get();

        // Buscar documentos específicos
        $informeDoc = Document::where('sample_id', $sample->id)
            ->where('type_document', 'informe')
            ->first();

        $cadenaDoc = Document::where('sample_id', $sample->id)
            ->where('type_document', 'cadena_custodia')
            ->first();

        return Inertia::render('sample/Show', [
            'sample' => $sample,
            'documents' => $documents,
            'informeDocument' => $informeDoc,
            'cadenaDocument' => $cadenaDoc,
        ]);
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

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $sample = Sample::findOrFail($document->sample_id);

        $contents = Storage::disk('s3')->get($document->document_archive);

        // Obtener la extensión real del archivo desde el path almacenado en S3
        $extension = pathinfo($document->document_archive, PATHINFO_EXTENSION);

        // Si no hay extensión, intentar detectar el tipo por el contenido
        if (empty($extension)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $contents);
            finfo_close($finfo);

            // Mapear MIME types a extensiones
            $mimeToExtension = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
            ];

            $extension = $mimeToExtension[$mimeType] ?? 'pdf';
        }

        // Definir el nombre del archivo según el tipo de documento con la extensión real
        $baseName = $document->type_document === 'informe'
            ? 'Informe-'.$sample->external_id
            : 'Cadena-Custodia-'.$sample->external_id;

        $fileName = $baseName.'.'.$extension;

        // Establecer el Content-Type apropiado
        $contentTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $contentType = $contentTypes[strtolower($extension)] ?? 'application/octet-stream';

        return response($contents, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }
}
