<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Quotation\Actions\BulkUpdateQuotationPassengersAction;
use App\Quotation\Actions\GenerateQuotationPassengersAction;
use App\Quotation\Http\Requests\QuotationPassenger\BulkUpdateQuotationPassengersRequest;
use App\Quotation\Http\Requests\QuotationPassenger\GenerateQuotationPassengersRequest;
use App\Quotation\Http\Resources\QuotationPassengerResource;
use App\Quotation\Models\Quotation;
use App\Quotation\Services\QuotationWorkflowService;
use DomainException;

class QuotationPassengerController extends Controller
{
    public function index(
        Quotation $quotation
    ) {
        $passengers = $quotation
                ->passengers()
                ->with('passengerType')
                ->orderBy('id')
                ->get();

        return QuotationPassengerResource::collection(
            $passengers
        );
    }

    public function generate(
        GenerateQuotationPassengersRequest $request,
        Quotation $quotation,
        GenerateQuotationPassengersAction $action
    ) {
        try {
            app(QuotationWorkflowService::class)->assertEditable($quotation);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $passengers =
            $action->execute(
                $quotation,
                $request->validated()
            );

        return QuotationPassengerResource::collection(
            collect($passengers)
        );
    }

    public function bulkUpdate(
        BulkUpdateQuotationPassengersRequest $request,
        Quotation $quotation,
        BulkUpdateQuotationPassengersAction $action
    ) {
        try {
            app(QuotationWorkflowService::class)->assertEditable($quotation);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $passengers =
            $action->execute(
                $quotation,
                $request->validated()[
                    'passengers'
                ]
            );

        return QuotationPassengerResource::collection(
            collect($passengers)
        );
    }
}
