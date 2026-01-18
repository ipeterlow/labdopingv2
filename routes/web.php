<?php

use App\Http\Controllers\BookHairSampleController;
use App\Http\Controllers\BookSalivaSampleController;
use App\Http\Controllers\BookUrineSampleController;
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
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas por autenticación y permisos
Route::middleware(['auth'])->group(function () {

    // Usuarios
    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store')
        ->middleware([HandlePrecognitiveRequests::class, 'permission:users.create']);
    Route::resource('users', UserController::class)
        ->middleware('permission:users.index');

    // Roles
    Route::resource('roles', RoleController::class)
        ->middleware('permission:roles.index');

    // Recepción de Muestras
    Route::resource('dopingsample', DopingSampleController::class)
        ->middleware('permission:dopingsample.index');
    Route::get('/dopingsample/{sample}/pdf', [DopingSampleController::class, 'download'])
        ->name('samples.pdf')
        ->middleware('permission:dopingsample.index');
    Route::put('/dopingsample/{id}/status', [DopingSampleController::class, 'updateStatus'])
        ->name('dopingsample.updateStatus')
        ->middleware('permission:dopingsample.edit');

    // Documentos
    Route::prefix('documents')->group(function () {
        Route::post('/upload-informe', [ReportSampleController::class, 'uploadInforme'])
            ->name('documents.upload.informe')
            ->middleware('permission:reportsample.edit');
        Route::post('/upload-cadena', [ReportSampleController::class, 'uploadCadena'])
            ->name('documents.upload.cadena')
            ->middleware('permission:reportsample.edit');
        Route::get('/download/{id}', [ReportSampleController::class, 'download'])
            ->name('documents.download')
            ->middleware('permission:reportsample.index');
    });

    // Informes de Muestras
    Route::resource('reportsample', ReportSampleController::class)
        ->middleware('permission:reportsample.index');

    // Reporte de Muestras
    Route::resource('sample', SampleController::class)
        ->middleware('permission:sample.index');

    Route::get('/download/{id}', [SampleController::class, 'download'])
        ->name('samples.download')
        ->middleware('permission:sample.index');

    // Libro Orina
    Route::get('/bookurinesample/export', [BookUrineSampleController::class, 'export'])
        ->name('bookurinesample.export')
        ->middleware('permission:bookurinesample.index');
    Route::resource('bookurinesample', BookUrineSampleController::class)
        ->middleware('permission:bookurinesample.index');
    Route::put('/bookurinesample/{id}/status', [BookUrineSampleController::class, 'updateStatus'])
        ->name('bookurinesample.updateStatus')
        ->middleware('permission:bookurinesample.edit');
    Route::put('/bookurinesample/{id}/results', [BookUrineSampleController::class, 'updateResults'])
        ->name('bookurinesample.updateResults')
        ->middleware('permission:bookurinesample.edit');

    // Libro Saliva
    Route::get('/booksalivasample/export', [BookSalivaSampleController::class, 'export'])
        ->name('booksalivasample.export')
        ->middleware('permission:booksalivasample.index');
    Route::resource('booksalivasample', BookSalivaSampleController::class)
        ->middleware('permission:booksalivasample.index');
    Route::put('/booksalivasample/{id}/status', [BookSalivaSampleController::class, 'updateStatus'])
        ->name('booksalivasample.updateStatus')
        ->middleware('permission:booksalivasample.edit');
    Route::put('/booksalivasample/{id}/results', [BookSalivaSampleController::class, 'updateResults'])
        ->name('booksalivasample.updateResults')
        ->middleware('permission:booksalivasample.edit');

    // Libro Pelo
    Route::get('/bookhairsample/export', [BookHairSampleController::class, 'export'])
        ->name('bookhairsample.export')
        ->middleware('permission:bookhairsample.index');
    Route::resource('bookhairsample', BookHairSampleController::class)
        ->middleware('permission:bookhairsample.index');
    Route::put('/bookhairsample/{id}/status', [BookHairSampleController::class, 'updateStatus'])
        ->name('bookhairsample.updateStatus')
        ->middleware('permission:bookhairsample.edit');
    Route::put('/bookhairsample/{id}/results', [BookHairSampleController::class, 'updateResults'])
        ->name('bookhairsample.updateResults')
        ->middleware('permission:bookhairsample.edit');

    // Permisos
    Route::resource('permissions', PermissionController::class)
        ->middleware('permission:permissions.index');

    // Empresas
    Route::resource('company', CompanyController::class)
        ->middleware('permission:company.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
