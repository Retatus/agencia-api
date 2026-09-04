<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\BaseCrudController;

use App\Quotation\Models\Quotation;
use App\Quotation\Filters\QuotationFilter;

use App\Quotation\Http\Resources\QuotationResource;

use App\Quotation\Http\Requests\Quotation\StoreQuotationRequest;
use App\Quotation\Http\Requests\Quotation\UpdateQuotationRequest;

use App\Quotation\Actions\CreateQuotationAction;
use App\Quotation\Actions\UpdateQuotationAction;

use Illuminate\Http\JsonResponse;

use App\Quotation\Calculation\Actions\CalculateQuotationAction;
use DomainException;
use Illuminate\Http\Request;


class QuotationController extends BaseCrudController
{
    protected string $model = Quotation::class;

    protected ?string $filter = QuotationFilter::class;

    protected string $resource = QuotationResource::class;

    protected string $storeRequest = StoreQuotationRequest::class;

    protected string $updateRequest = UpdateQuotationRequest::class;

    protected string $routeKey = 'uuid';

    /**
     * Relaciones que siempre se cargarán.
     */
    protected array $with = [
        'customer',
        'currency',
        'status',
        'passengers.passengerType',
        'itineraries.items',
    ];

    public function __construct(
        protected CreateQuotationAction $createQuotationAction,
        protected UpdateQuotationAction $updateQuotationAction
    ) {
    }

    /**
     * Crear una cotización.
     */   

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $quotation = $this->createQuotationAction
            ->execute($request->validated());

        return response()->json([
            'message' => 'Cotización creada correctamente.',
            'data' => new QuotationResource($quotation),
        ], 201);
    }


    /**
     * Actualizar una cotización.
     */

    public function update(UpdateQuotationRequest $request, Quotation $quotation): JsonResponse
    {
        $quotation = $this->updateQuotationAction
            ->execute($quotation, $request->validated());

        return response()->json([
            'message' => 'Cotización actualizada correctamente.',
            'data' => new QuotationResource($quotation),
        ]);
    }

    public function calculate(
        Request $request,
        CalculateQuotationAction $action
    ): JsonResponse
    {
        try {
            $result = $action->execute(
                $request->all()
            );
        } catch (DomainException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
