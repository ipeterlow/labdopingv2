<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DopingSampleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportSampleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/users', [UserController::class, 'store'])
    ->name('users.store')
    ->middleware(HandlePrecognitiveRequests::class);
Route::resource('users', UserController::class);

Route::resource('roles', RoleController::class);

Route::resource('dopingsample', DopingSampleController::class);
Route::get('/dopingsample/{sample}/pdf', [DopingSampleController::class, 'download'])->name('samples.pdf');

Route::prefix('documents')->group(function () {
    Route::post('/upload-informe', [ReportSampleController::class, 'uploadInforme'])
        ->name('documents.upload.informe');
    Route::post('/upload-cadena', [ReportSampleController::class, 'uploadCadena'])
        ->name('documents.upload.cadena');
    Route::get('/download/{id}', [ReportSampleController::class, 'download'])
        ->name('documents.download');
});

Route::resource('reportsample', ReportSampleController::class);
Route::resource('sample', SampleController::class);

Route::resource('permissions', PermissionController::class);

Route::resource('company', CompanyController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
