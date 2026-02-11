<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Optimizaciones de rendimiento para producción
        if ($this->app->environment('production')) {
            // Deshabilitar lazy loading para detectar N+1 queries
            Model::preventLazyLoading(false);

            // No registrar queries en producción
            DB::disableQueryLog();
        } else {
            // En desarrollo, prevenir lazy loading para detectar N+1
            Model::preventLazyLoading(true);

            // Loguear queries lentas (más de 500ms) en desarrollo
            DB::whenQueryingForLongerThan(500, function ($connection, $event) {
                \Log::warning('Query lenta detectada', [
                    'sql' => $event->sql,
                    'bindings' => $event->bindings,
                    'time' => $event->time,
                ]);
            });
        }

        // Configurar defaults para Eloquent
        Model::shouldBeStrict(! $this->app->environment('production'));
    }
}
