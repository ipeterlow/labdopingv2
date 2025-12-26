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
        return redirect()->route('dopingsample.index');
    }

    return redirect()->route('login');
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
Route::put('/dopingsample/{id}/status', [DopingSampleController::class, 'updateStatus'])->name('dopingsample.updateStatus');

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

Route::resource('bookurinesample', BookUrineSampleController::class);
Route::put('/bookurinesample/{id}/status', [BookUrineSampleController::class, 'updateStatus'])->name('bookurinesample.updateStatus');
Route::put('/bookurinesample/{id}/results', [BookUrineSampleController::class, 'updateResults'])->name('bookurinesample.updateResults');

Route::resource('booksalivasample', BookSalivaSampleController::class);
Route::put('/booksalivasample/{id}/status', [BookSalivaSampleController::class, 'updateStatus'])->name('booksalivasample.updateStatus');
Route::put('/booksalivasample/{id}/results', [BookSalivaSampleController::class, 'updateResults'])->name('booksalivasample.updateResults');

Route::resource('bookhairsample', BookHairSampleController::class);
Route::put('/bookhairsample/{id}/status', [BookHairSampleController::class, 'updateStatus'])->name('bookhairsample.updateStatus');
Route::put('/bookhairsample/{id}/results', [BookHairSampleController::class, 'updateResults'])->name('bookhairsample.updateResults');

Route::resource('permissions', PermissionController::class);

Route::resource('company', CompanyController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
