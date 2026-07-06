<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\BaseCrudController;

use App\Quotation\Models\Quotation;
use App\Quotation\Filters\QuotationFilter;

use App\Quotation\Http\Resources\QuotationResource;

use App\Quotation\Http\Requests\Quotation\UpdateQuotationRequest;
use App\Quotation\Http\Requests\Quotation\StoreQuotationRequest;

class QuotationController extends BaseCrudController
{
    protected string $model = Quotation::class;

    protected ?string $filter = QuotationFilter::class;

    protected string $resource = QuotationResource::class;

    protected string $storeRequest = StoreQuotationRequest::class;

    protected string $updateRequest = UpdateQuotationRequest::class;

    /**
     * Relaciones que siempre se cargarán.
     */
    protected array $with = [
        'customer',
        'currency',
        'priceList',
        'status',
        'passengers.passengerType',
        'items.serviceVariant.service.provider',
        'items.price.priceType',
    ];
}