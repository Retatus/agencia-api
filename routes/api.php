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

use App\Pricing\Price\Controllers\PriceController;
use App\Pricing\PriceList\Controllers\PriceListController;
use App\Pricing\PriceType\Controllers\PriceTypeController;

use App\Http\Controllers\CRM\CustomerController;

use App\Quotation\Http\Controllers\QuotationController;
use App\Quotation\Http\Controllers\QuotationItineraryController;
use App\Quotation\Http\Controllers\QuotationStatusController;

use App\Quotation\Http\Controllers\QuotationItemController;
use App\Quotation\Http\Controllers\QuotationPassengerController;

use App\Audit\Http\Controllers\HistoryController;

use App\Pricing\BasePrice\Controllers\BasePriceController;
use App\Pricing\PriceListItem\Controllers\PriceListItemController;


Route::group([], function () {
    Route::apiResource('paises', PaisController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('passenger-types', PassengerTypeController::class);
    Route::get('document-types/select', [DocumentTypeController::class, 'select']);
    Route::apiResource('document-types', DocumentTypeController::class);

    Route::get('service-categories/select', [ServiceCategoryController::class, 'select']);
    Route::apiResource('service-categories', ServiceCategoryController::class);

    Route::get('services/search', [ServiceController::class, 'search']);
    Route::get('services/{service}/variants', [ServiceController::class, 'variants']);
    Route::get('services/{service}/variants/{variant_id}/prices', [ServiceController::class, 'prices']);
    Route::apiResource('services', ServiceController::class);
    
    Route::apiResource('service-variants', ServiceVariantController::class);
    // Catalog / Services / Variants
    Route::prefix('catalog/services/{service:uuid}/variants')->group(function () {
        Route::get('/', [ServiceVariantController::class, 'index']);
        Route::post('/', [ServiceVariantController::class, 'store']);
        Route::get('/{variant}', [ServiceVariantController::class, 'show']);
        Route::put('/{variant}', [ServiceVariantController::class, 'update']);
        Route::delete('/{variant}', [ServiceVariantController::class, 'destroy']);
    }); 

    Route::get('providers/select', [ProviderController::class, 'select']);
    Route::apiResource('providers', ProviderController::class);

    Route::prefix('pricing')->group(function () {

    /*
        |--------------------------------------------------------------------------
        | PRICE LIST ITEMS
        |--------------------------------------------------------------------------
        */

        Route::get('price-lists/{priceList:uuid}/items',[PriceListItemController::class, 'index']);
        Route::post('price-lists/{priceList:uuid}/items',[PriceListItemController::class, 'store']);
        Route::put('price-lists/{priceList:uuid}/items/{item}',[PriceListItemController::class, 'update']);
        Route::delete('price-lists/{priceList:uuid}/items/{item}',[PriceListItemController::class, 'destroy']);
        
        /*
        |--------------------------------------------------------------------------
        | PRICE LISTS
        |--------------------------------------------------------------------------
        */

        Route::apiResource('price-lists', PriceListController::class);

        /*
        |--------------------------------------------------------------------------
        | PRICES LEGACY / ACTUAL
        |--------------------------------------------------------------------------
        */

        Route::patch('prices/bulk', [PriceController::class, 'bulkUpdate']);
        Route::apiResource('prices', PriceController::class);

        Route::apiResource('price-types', PriceTypeController::class);

        /*
        |--------------------------------------------------------------------------
        | BASE PRICE
        |--------------------------------------------------------------------------
        */

        Route::get('service-variants/{variant}/base-price',[BasePriceController::class, 'show']);
        Route::post('service-variants/{variant}/base-price',[BasePriceController::class, 'store']);
        Route::put('service-variants/{variant}/base-price',[BasePriceController::class, 'update']);
        Route::delete('service-variants/{variant}/base-price',[BasePriceController::class, 'destroy']);
        
    });

    // no afecta en dada el echo de agregar customer:uuid en las rutas
    Route::prefix('crm')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    Route::post('quotations/calculate',[QuotationController::class, 'calculate']);
    Route::get('quotations/statuses', [QuotationStatusController::class, 'index']);
    Route::prefix('quotations/{quotation:uuid}')->group(function () {
        Route::post('passengers/generate', [QuotationPassengerController::class, 'generate']);
        Route::patch('passengers/bulk', [QuotationPassengerController::class, 'bulkUpdate']);
        Route::get('passengers', [QuotationPassengerController::class, 'index']);
    });
    Route::prefix('quotations')->group(function () {
        Route::apiResource('/', QuotationController::class)->parameters(['' => 'quotation']);
    });
    
    Route::prefix('quotations-itineraries')->group(function () {
        Route::apiResource('/', QuotationItineraryController::class)->parameters(['' => 'quotitationItinerary']);
    });
    
    Route::delete('/quotation-items/{quotationItem}', [QuotationItemController::class, 'destroy']);

    Route::prefix('audit')->group(function () {
        Route::get('history/{uuid}/view',[HistoryController::class, 'index']);
    });


// GET    /api/v1/catalog/service-variants/{variant}/base-price
// POST   /api/v1/catalog/service-variants/{variant}/base-price
// PUT    /api/v1/catalog/service-variants/{variant}/base-price
// DELETE /api/v1/catalog/service-variants/{variant}/base-price

// GET    /api/v1/pricing/price-lists/{priceList}/items
// POST   /api/v1/pricing/price-lists/{priceList}/items
// PUT    /api/v1/pricing/price-lists/{priceList}/items/{item}
// DELETE /api/v1/pricing/price-lists/{priceList}/items/{item}
    
       
});