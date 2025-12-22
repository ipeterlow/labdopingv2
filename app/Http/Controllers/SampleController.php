<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Sample;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $currentTeamId = auth()->user()->current_team_id;

        if ($currentTeamId === null || $currentTeamId == '') {
            $samples = Sample::join('companies', 'samples.company_id', '=', 'companies.id')
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
                ->orderBy('samples.id', 'desc')
                ->get();
        } else {
            $samples = Sample::where('samples.company_id', $currentTeamId)
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
                ->orderBy('samples.id', 'desc')
                ->get();
        }

        // Calcular tiempos para cada muestra
        $samples = $samples->map(function ($sample) {
            // Tiempo de recepción (días entre sent_at y received_at)
            if ($sample->sent_at && $sample->received_at) {
                $sentDate = \Carbon\Carbon::parse($sample->sent_at);
                $receivedDate = \Carbon\Carbon::parse($sample->received_at);
                $sample->tiempo_recepcion = (int) $sentDate->diffInDays($receivedDate);
            } else {
                $sample->tiempo_recepcion = null;
            }

            // Tiempo de respuesta (días entre received_at y informe created_at)
            if ($sample->received_at && $sample->informe_created_at) {
                $receivedDate = \Carbon\Carbon::parse($sample->received_at);
                $informeDate = \Carbon\Carbon::parse($sample->informe_created_at);
                $sample->tiempo_respuesta = (int) $receivedDate->diffInDays($informeDate);
            } else {
                $sample->tiempo_respuesta = null;
            }

            return $sample;
        });

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
}
