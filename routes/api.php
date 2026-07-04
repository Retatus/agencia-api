<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaisController;
use App\Http\Controllers\Shared\CurrencyController;
use App\Http\Controllers\Shared\PassengerTypeController;
use App\Http\Controllers\Shared\DocumentTypeController;

use App\Http\Controllers\Catalog\ServiceCategoryController;
use App\Http\Controllers\Catalog\ServiceController;
use App\Http\Controllers\Catalog\ServiceVariantController;
use App\Http\Controllers\Catalog\ProviderController;

// php artisan serve --port=8001

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('passenger-types', PassengerTypeController::class);
    Route::apiResource('document-types', DocumentTypeController::class);

    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('service-variants', ServiceVariantController::class);
    Route::apiResource('providers', ProviderController::class);
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
  * Para un request de actualizacion en el path validar con sometimes y no con required, para que no sea obligatorio enviar todos los campos
  * agregar el resource, PassengerTypeResource
  * agregar las rutas en api.php
  */