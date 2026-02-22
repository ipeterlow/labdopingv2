<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $rawData = $this->getRawData();

        return Inertia::render('Dashboard', [
            'muestrasPorMesAno' => $this->formatByYear($rawData),
            'muestrasOrina' => $this->formatByYear($rawData, 'orina'),
            'muestrasPelo' => $this->formatByYear($rawData, 'pelo'),
            'muestrasSaliva' => $this->formatByYear($rawData, 'saliva'),
            'muestrasEnProceso' => $this->getMuestrasEnProceso(),
        ]);
    }

    /**
     * Muestras en proceso de análisis (status 1, 3, 5) del año actual, agrupadas por tipo.
     * Una sola query para los 3 conteos.
     */
    private function getMuestrasEnProceso(): array
    {
        $currentYear = (int) now()->year;

        $data = DB::table('samples')
            ->selectRaw('type, COUNT(*) as total')
            ->whereIn('status', [1, 3, 5])
            ->whereNull('deleted_at')
            ->whereNotNull('analyzed_at')
            ->where('analyzed_at', '!=', '')
            ->whereRaw('YEAR(created_at) = ?', [$currentYear])
            ->whereIn('type', ['orina', 'pelo', 'saliva'])
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'orina' => (int) ($data['orina'] ?? 0),
            'pelo' => (int) ($data['pelo'] ?? 0),
            'saliva' => (int) ($data['saliva'] ?? 0),
        ];
    }

    /**
     * Una sola query que trae todo: año, mes, tipo y total.
     * Se reutiliza para todos los gráficos.
     */
    private function getRawData()
    {
        return DB::table('samples')
            ->selectRaw('YEAR(received_at) as year, MONTH(received_at) as month, type, COUNT(*) as total')
            ->whereNotNull('received_at')
            ->whereNull('deleted_at')
            ->groupByRaw('YEAR(received_at), MONTH(received_at), type')
            ->orderByRaw('YEAR(received_at), MONTH(received_at)')
            ->get();
    }

    /**
     * Formatea los datos agrupados por año.
     * Si se pasa $type, filtra solo ese tipo. Si es null, suma todos.
     */
    private function formatByYear($rawData, ?string $type = null): array
    {
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        $result = [];
        foreach ($rawData as $row) {
            if ($type !== null && $row->type !== $type) {
                continue;
            }

            $year = (int) $row->year;
            $month = (int) $row->month;

            if (! isset($result[$year])) {
                $maxMonth = ($year < $currentYear) ? 12 : $currentMonth;
                $months = [];
                for ($m = 1; $m <= 12; $m++) {
                    $months[$m] = ($m <= $maxMonth) ? 0 : null;
                }
                $result[$year] = $months;
            }

            $result[$year][$month] += (int) $row->total;
        }

        $formatted = [];
        foreach ($result as $year => $months) {
            $formatted[] = [
                'year' => $year,
                'data' => array_values($months),
            ];
        }

        usort($formatted, fn ($a, $b) => $a['year'] <=> $b['year']);

        return $formatted;
    }
}
