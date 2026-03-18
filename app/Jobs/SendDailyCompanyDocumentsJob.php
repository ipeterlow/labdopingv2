<?php

namespace App\Jobs;

use App\Mail\DailyCompanyDocumentsMail;
use App\Models\Company;
use App\Models\CompanyEmailContact;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyCompanyDocumentsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Carbon $date;

    public function __construct(?Carbon $date = null)
    {
        $this->date = $date?->copy() ?? Carbon::today();
    }

    public function handle(): void
    {
        $startedAt = Carbon::now();

        $documents = Document::query()
            ->whereDate('created_at', $this->date->toDateString())
            ->where('type_document', 'informe')
            ->with(['sample.company'])
            ->get();

        if ($documents->isEmpty()) {
            return;
        }

        $byCompany = $documents->groupBy(function (Document $document) {
            return $document->sample?->company_id;
        });

        $totalCompanies = 0;
        $totalEmails = 0;

        foreach ($byCompany as $companyId => $companyDocuments) {
            if (! $companyId) {
                continue;
            }

            /** @var Company|null $company */
            $company = Company::find($companyId);
            if (! $company) {
                continue;
            }

            $contacts = CompanyEmailContact::query()
                ->active()
                ->where('company_id', $companyId)
                ->pluck('email')
                ->map(function (?string $email) use ($company) {
                    if ($email === null) {
                        return null;
                    }

                    // Eliminar todos los espacios y caracteres de control del correo
                    $normalized = preg_replace('/\s+/', '', $email ?? '');

                    if (empty($normalized)) {
                        Log::channel('mail')->warning('Email vacío después de normalizar', [
                            'company_id' => $company->id,
                            'raw_email' => $email,
                        ]);

                        return null;
                    }

                    if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                        Log::channel('mail')->warning('Email inválido para envío de informes diarios', [
                            'company_id' => $company->id,
                            'raw_email' => $email,
                            'normalized_email' => $normalized,
                        ]);

                        return null;
                    }

                    return $normalized;
                })
                ->filter()
                ->unique()
                ->values();

            if ($contacts->isEmpty()) {
                Log::channel('mail')->info('Empresa sin emails válidos para envío de informes diarios', [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                ]);

                continue;
            }

            /** @var Collection<int, array<string, mixed>> $samples */
            $samples = $companyDocuments
                ->map(function (Document $document) {
                    $sample = $document->sample;

                    return [
                        'external_id' => $sample?->external_id,
                        'type' => $sample?->type,
                        'document_created_at' => $document->created_at,
                        'type_document' => $document->type_document ?? null,
                        'document_status' => $document->status ?? null,
                    ];
                })
                ->unique(fn (array $item) => $item['external_id'].'|'.$item['document_created_at'])
                ->values();

            if ($samples->isEmpty()) {
                continue;
            }

            $totalCompanies++;
            $totalEmails += $contacts->count();

            $to = $contacts->all();

            if (empty($to)) {
                continue;
            }

            Mail::to($to)
                ->send(new DailyCompanyDocumentsMail($company, $samples, $this->date));
        }

        $endedAt = Carbon::now();

        Log::channel('mail')->info('Envio diario de informes completado', [
            'date' => $this->date->toDateString(),
            'companies_notified' => $totalCompanies,
            'emails_sent' => $totalEmails,
            'started_at' => $startedAt->toDateTimeString(),
            'ended_at' => $endedAt->toDateTimeString(),
            'duration_seconds' => $endedAt->diffInSeconds($startedAt),
        ]);
    }
}
