<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sample;
use App\Models\Document;
use Inertia\Inertia;


class SampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $currentTeamId = auth()->user()->current_team_id;

        if($currentTeamId === null || $currentTeamId == '') {
               $samples = Sample::join('companies', 'samples.company_id', '=', 'companies.id')
            ->join('sample_status', 'samples.status', '=', 'sample_status.id')
            ->select('samples.id', 'samples.external_id', 'samples.internal_id', 'samples.category', 'sample_status.name as status_name', 'samples.type', 'samples.sent_at', 'samples.received_at', 'samples.analyzed_at', 'samples.sample_taken_at', 'samples.results_at', 'companies.name as company_name')
            ->orderBy('samples.id', 'desc')
            ->get();
        }else{
            $samples = Sample::where('company_id', $currentTeamId)
                ->join('companies', 'samples.company_id', '=', 'companies.id')
                ->join('sample_status', 'samples.status', '=', 'sample_status.id')
                ->select('samples.id', 'samples.external_id', 'samples.internal_id', 'samples.category', 'sample_status.name as status_name', 'samples.type', 'samples.sent_at', 'samples.received_at', 'samples.analyzed_at', 'samples.sample_taken_at', 'samples.results_at', 'companies.name as company_name')
                ->orderBy('samples.id', 'desc')
                ->get();
        }
     

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
