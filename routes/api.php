<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaisController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\PassengerTypeController;

// php artisan serve --port=8001

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('passenger-types', PassengerTypeController::class);
});



// php artisan make:model PassengerType
// php artisan make:resource PassengerTypeResource
// php artisan make:request Currency/StorePassengerTypeRequest
// php artisan make:request Currency/UpdatePassengerTypeRequest
// php artisan make:controller Api/PassengerTypeController --api

// php artisan route:list --path=api