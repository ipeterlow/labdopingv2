<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        // Comparte auth.user, auth.permissions y auth.roles con Inertia
        Inertia::share('auth', function () {
            $user = auth()->user();

            return [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,

                // Todos los permisos resultantes (directos + heredados por rol)
                'permissions' => $user
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [],

                // Nombres de roles del usuario
                'roles' => $user
                    ? $user->getRoleNames()->toArray()
                    : [],
            ];
        });

        // Comparte mensajes flash con Inertia
        Inertia::share('flash', function () {
            return [
                'success' => session('success'),
                'error' => session('error'),
            ];
        });
    }
}
