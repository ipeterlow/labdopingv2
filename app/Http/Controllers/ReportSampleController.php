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

    /**
     * 🧱 Método reutilizable para ambas subidas
     */
    private function handleUpload(Request $request, string $type)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:12288', // máx. 12 MB
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

        // Obtener archivo y nombre original
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        // Carpeta en S3 según tipo
        $folder = $type === 'informe' ? 'informes' : 'cadenas';

        // Guardar en S3 usando el disco configurado
        $path = $file->storeAs("{$folder}", $filename, 's3');

        // Crear registro de documento
        $document = Document::create([
            'document_archive' => $path,
            'sample_id' => $sample->id,
        ]);

        // Actualizar el estado de la muestra (opcional, igual que en tu código original)
        $sample->update([
            'document' => $document->id,
            'status' => $type === 'informe' ? 2 : 4,
            'results_at' => Carbon::now('America/Santiago'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Archivo '{$filename}' subido correctamente.",
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

        return response()->streamDownload(
            fn () => print ($contents),
            'Informe-'.$sample->external_id.'.pdf'
        );
    }
}
