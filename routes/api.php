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

use App\Http\Controllers\Pricing\PriceController;
use App\Http\Controllers\Pricing\PriceListController;
use App\Http\Controllers\Pricing\PriceTypeController;

use App\Http\Controllers\CRM\CustomerController;

use App\Quotation\Http\Controllers\QuotationController;

// php artisan serve --port=8001

Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('passenger-types', PassengerTypeController::class);
    Route::get('document-types/select', [DocumentTypeController::class, 'select']);
    Route::apiResource('document-types', DocumentTypeController::class);

    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('service-variants', ServiceVariantController::class);
    Route::apiResource('providers', ProviderController::class);

    Route::prefix('pricing')->group(function () {
        Route::apiResource('price-lists', PriceListController::class);
        Route::apiResource('prices', PriceController::class);
        Route::apiResource('price-types', PriceTypeController::class);
    });

    Route::prefix('crm')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    Route::prefix('quotations')->group(function () {
        Route::apiResource('/', QuotationController::class)->parameters(['' => 'quotation']);
    });     
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