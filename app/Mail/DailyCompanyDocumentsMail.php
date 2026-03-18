<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DailyCompanyDocumentsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Company $company;

    /**
     * @var Collection<int, array<string, mixed>>
     */
    public Collection $samples;

    public Carbon $date;

    /**
     * @param  Collection<int, array<string, mixed>>  $samples
     */
    public function __construct(Company $company, Collection $samples, Carbon $date)
    {
        $this->company = $company;
        $this->samples = $samples;
        $this->date = $date;
    }

    public function build(): self
    {
        return $this->subject(
            'Informes disponibles '.$this->date->format('d-m-Y').' - '.$this->company->name
        )->view('emails.daily_company_documents')
            ->with([
                'company' => $this->company,
                'samples' => $this->samples,
                'date' => $this->date,
            ]);
    }
}
