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

use App\Quotation\Http\Controllers\QuotationItemController;

use App\Audit\Http\Controllers\HistoryController;



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
    Route::get('providers/select', [ProviderController::class, 'select']);
    Route::apiResource('providers', ProviderController::class);

    Route::prefix('pricing')->group(function () {
        Route::apiResource('price-lists', PriceListController::class);
        Route::patch('prices/bulk', [PriceController::class, 'bulkUpdate']);
        Route::apiResource('prices', PriceController::class);
        Route::apiResource('price-types', PriceTypeController::class);
    });

    // no afecta en dada el echo de agregar customer:uuid en las rutas
    Route::prefix('crm')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    // Route::get('crm/customers', [CustomerController::class, 'index'])->name('customers.index');
    // Route::get('crm/customers/{customer:uuid}', [CustomerController::class, 'show'])->name('customers.show');
    // Route::post('crm/customers', [CustomerController::class, 'store'])->name('customers.store');
    // Route::put('crm/customers/{customer:uuid}', [CustomerController::class, 'update'])->name('customer.update');
    // Route::patch('crm/customers/{customer:uuid}', [CustomerController::class, 'update'])->name('customer.update');
    // Route::delete('crm/customers/{customer:uuid}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('quotations/calculate',[QuotationController::class, 'calculate']);
    Route::prefix('quotations')->group(function () {
        Route::apiResource('/', QuotationController::class)->parameters(['' => 'quotation']);
    });
    
    Route::prefix('quotations-itineraries')->group(function () {
        Route::apiResource('/', QuotationItineraryController::class)->parameters(['' => 'quotitationItinerary']);
    });
    // Route::get('quotations-itineraries/{quotitationItinerary}/items', [QuotationItineraryController::class, 'items'])->name('quotations-itineraries.items');
    // Route::post('quotations-itineraries/{quotitationItinerary}/items', [QuotationItineraryController::class, 'itemsStore'])->name('quotations-itineraries.items.store');
    // Route::put('quotations-itineraries/{quotitationItinerary}/items/{item}', [QuotationItineraryController::class, 'itemsUpdate'])->name('quotations-itineraries.items.update');
    // Route::delete('quotations-itineraries/{quotitationItinerary}/items/{item}', [QuotationItineraryController::class, 'itemsDestroy'])->name('quotations-itineraries.items.destroy');
    
    Route::delete('/quotation-items/{quotationItem}', [QuotationItemController::class, 'destroy']);

    Route::prefix('audit')->group(function () {
        Route::get('history/{uuid}/view',[HistoryController::class, 'index']);
    });

    //   const response = await api.get(`/api/v1/audit/history/${uuid}`, {
  //     params,
  //   })

    
});