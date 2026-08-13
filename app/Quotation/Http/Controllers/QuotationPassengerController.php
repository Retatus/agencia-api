<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Quotation\Actions\BulkUpdateQuotationPassengersAction;
use App\Quotation\Actions\GenerateQuotationPassengersAction;
use App\Quotation\Http\Requests\QuotationPassenger\BulkUpdateQuotationPassengersRequest;
use App\Quotation\Http\Requests\QuotationPassenger\GenerateQuotationPassengersRequest;
use App\Quotation\Http\Resources\QuotationPassengerResource;
use App\Quotation\Models\Quotation;

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