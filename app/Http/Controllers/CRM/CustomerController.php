<?php

namespace App\Http\Controllers\CRM;

use App\Filters\CRM\CustomerFilter;
use App\Http\Controllers\BaseCrudController;
use App\Http\Resources\CRM\CustomerResource;
use App\Models\CRM\Customer;

class CustomerController extends BaseCrudController
{
    protected string $model = Customer::class;

    protected string $resource = CustomerResource::class;

    protected ?string $filter = CustomerFilter::class;

    protected array $with = [
        'documentType'
    ];

    protected string $defaultSort = 'last_name';
}
