<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaisController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\PassengerTypeController;
use App\Http\Controllers\Api\DocumentTypeController;

// php artisan serve --port=8001

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('passenger-types', PassengerTypeController::class);
    Route::apiResource('document-types', DocumentTypeController::class);
});



// php artisan make:model PassengerType
// php artisan make:controller Api/PassengerTypeController --api
// php artisan make:request PassengerType/StorePassengertTypeRequest
// php artisan make:request PassengerType/UpdatePassengertTypeRequest
// php artisan make:resource PassengerTypeResource

// php artisan route:list --path=api

 /**
  * agregar las propiedades del modelo, id, name, code, description, active
  * modificar los metodos del contoller, index, store, show, update, destroy
  * agregar los request, StorePassengerTypeRequest, UpdatePassengerTypeRequest (authorize = true, rules, messages)
  * agregar el resource, PassengerTypeResource
  * agregar las rutas en api.php
  */