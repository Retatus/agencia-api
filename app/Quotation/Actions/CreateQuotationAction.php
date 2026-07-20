<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class CreateQuotationAction
{
    public function __construct(
        protected CreateQuotationHeaderAction $headerAction,
        protected CreateQuotationItinerariesAction $itineraryAction,
        protected CreateQuotationPassengersAction $passengerAction,
        protected CalculateQuotationTotalsAction $totalsAction,
    ) {}

    public function execute(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            // Cabecera
            $quotation = $this->headerAction->execute($data);

            // Itinerarios + Items
            $this->itineraryAction->execute(
                $quotation,
                $data['itineraries'] ?? []
            );

            // Pasajeros
            $this->passengerAction->execute(
                $quotation,
                $data['passengers'] ?? []
            );

            // Totales
            $quotation = $this->totalsAction->execute($quotation);

            return $quotation->fresh()->load([
                'customer',
                'currency',
                'priceList',
                'status',

                'itineraries',
                'itineraries.items',

                'passengers',
                'passengers.passengerType',
            ]);
        });
    }
}