<?php

namespace App\Http\Controllers\CRM;

use App\Filters\CRM\CustomerFilter;
use App\Http\Controllers\BaseCrudController;
use App\Http\Requests\CRM\Customer\StoreCustomerRequest;
use App\Http\Requests\CRM\Customer\UpdateCustomerRequest;
use App\Http\Resources\CRM\CustomerResource;
use Illuminate\Http\JsonResponse;
use App\Models\CRM\Customer;

use Illuminate\Http\Request;

class CustomerController extends BaseCrudController
{
    protected string $model = Customer::class;

    protected string $resource = CustomerResource::class;

    protected ?string $filter = CustomerFilter::class;

    protected array $with = [
        'documentType'
    ];

    protected string $defaultSort = 'last_name';

    /**
     * Crear una cotización.
     */
    public function store(request $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json([
            'message' => 'Cliente creados correctamente.',
            'customer' => new CustomerResource($customer),
        ], 201);
    }


    /**
     * Actualizar una cotización.
     */
    public function update(request $request, string $id): JsonResponse
    {
        $customer = Customer::update($request->validated());

        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'customer' => new CustomerResource($customer),
        ]);
    }
}
