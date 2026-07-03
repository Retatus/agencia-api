<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaisController;
use App\Http\Controllers\Api\CurrencyController;

// php artisan serve --port=8001

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
});
