<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/users', [UserController::class, 'store'])
    ->name('users.store')
    ->middleware(HandlePrecognitiveRequests::class);
Route::resource('users', UserController::class);

Route::resource('roles', RoleController::class);

Route::resource('permissions', PermissionController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
