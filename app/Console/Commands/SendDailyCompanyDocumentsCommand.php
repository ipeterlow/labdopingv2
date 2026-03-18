<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyCompanyDocumentsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDailyCompanyDocumentsCommand extends Command
{
    protected $signature = 'reports:send-daily-company-documents {date?}';

    protected $description = 'Envía el resumen diario de documentos por empresa a los contactos configurados';

    public function handle(): int
    {
        $dateInput = $this->argument('date');

        $date = $dateInput
            ? Carbon::parse($dateInput)->startOfDay()
            : Carbon::today('America/Santiago');

        SendDailyCompanyDocumentsJob::dispatch($date);

        $this->info('Job de envío de informes diarios encolado para la fecha '.$date->toDateString().'.');

        return self::SUCCESS;
    }
}
