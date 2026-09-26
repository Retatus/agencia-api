<?php

namespace App\Http\Controllers\CRM;

use App\Filters\CRM\CustomerFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\Customer\StoreCustomerRequest;
use App\Http\Requests\CRM\Customer\UpdateCustomerRequest;
use App\Http\Resources\CRM\CustomerResource;
use App\Models\CRM\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Listado de clientes.
     */
    public function index(Request $request)
    {
        $query = Customer::query()
            ->with(['documentType', 'country']);

        $query = (new CustomerFilter($request))->apply($query);

        $customers = $query->paginate(
            $request->integer('per_page', 20)
        );

        return CustomerResource::collection($customers);
    }

    /**
     * Mostrar un cliente.
     */
    public function show(Customer $customer): CustomerResource
    {
        $customer->load('documentType');

        return new CustomerResource($customer);
    }

    /**
     * Crear un cliente.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        $customer->load('documentType');

        return response()->json([
            'message' => 'Cliente creado correctamente.',
            'customer' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Actualizar un cliente.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse {

        $customer->update($request->validated());

        $customer->load('documentType');

        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Eliminar un cliente.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }
}