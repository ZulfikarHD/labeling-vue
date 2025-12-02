<?php

use App\Http\Controllers\Api\SpecificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes untuk API yang diakses oleh frontend dan external systems.
| Routes ini menggunakan prefix /api secara otomatis.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Specification Routes (SIRINE API Integration)
|--------------------------------------------------------------------------
|
| Routes untuk fetch dan validate specifications dari SIRINE API
| yang digunakan oleh frontend untuk mendapatkan data produk.
|
*/
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/specifications/{poNumber}', [SpecificationController::class, 'show'])
        ->name('api.specifications.show')
        ->where('poNumber', '[0-9]+');

    Route::get('/specifications/{poNumber}/validate', [SpecificationController::class, 'validate'])
        ->name('api.specifications.validate')
        ->where('poNumber', '[0-9]+');

    Route::get('/specifications/{poNumber}/raw', [SpecificationController::class, 'raw'])
        ->name('api.specifications.raw')
        ->where('poNumber', '[0-9]+');
});
