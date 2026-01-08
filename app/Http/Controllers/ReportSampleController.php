<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Sample;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ReportSampleController extends Controller
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

        return Inertia::render('reportsample/Index', [
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

        return Inertia::render('reportsample/Show', [
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

    /**
     * 📄 Sube un Informe de Muestra a S3
     */
    public function uploadInforme(Request $request)
    {
        return $this->handleUpload($request, 'informe');
    }

    /**
     * 🧾 Sube una Cadena de Custodia a S3
     */
    public function uploadCadena(Request $request)
    {
        return $this->handleUpload($request, 'cadena');
    }

    private function handleUpload(Request $request, string $type)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:12288', // máx. 12 MB - Acepta PDFs e imágenes
            'external_id' => 'required',
        ]);

        // Buscar la muestra asociada
        $sample = Sample::where('id', 'LIKE', $request->external_id)->first();

        if (! $sample) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró una muestra con el ID proporcionado.',
            ], 404);
        }

        // --- INICIO DE LA CORRECCIÓN ---

        // 1. Obtener archivo, nombre y carpeta
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $folder = $type === 'informe' ? 'informes' : 'cadenas';

        // 2. Definir el tipo de documento (¡Corregí un typo: era 'candena'!)
        $documentType = $type === 'informe' ? 'informe' : 'cadena_custodia';

        // 3. Buscar si ya existe un documento de este tipo para esta muestra
        $existingDocument = Document::where('sample_id', $sample->id)
            ->where('type_document', $documentType)
            ->first();

        // 4. Sube el NUEVO archivo a S3 (siempre)
        $path = $file->storeAs("{$folder}", $filename, 's3');

        // 5. Si existía un documento, borra el archivo ANTIGUO de S3
        if ($existingDocument && $existingDocument->document_archive) {
            // Usamos Storage::disk('s3') para asegurarnos
            Storage::disk('s3')->delete($existingDocument->document_archive);
        }

        $document = Document::updateOrCreate(
            [
                'sample_id' => $sample->id,
                'type_document' => $documentType,
            ],
            [
                'document_archive' => $path,
            ]
        );

        // --- FIN DE LA CORRECCIÓN ---

        // 7. Actualizar el estado de la muestra
        // (Mejora: Solo actualiza el estado a "listo" si es un informe)
        if ($type === 'informe') {
            $sample->update([
                'document' => $document->id,
                'status' => 4,
                'results_at' => Carbon::now('America/Santiago'),
            ]);
        }
        // Nota: Si subes una 'cadena', la muestra no cambiará de estado
        // pero el documento 'cadena_custodia' sí se habrá guardado/actualizado.

        return response()->json([
            'status' => 'success',
            'message' => "Archivo '{$filename}' subido y actualizado correctamente.",
            'path' => $path,
            'document_id' => $document->id,
        ]);
    }

    /**
     * 📥 Descargar archivo desde S3
     */
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
