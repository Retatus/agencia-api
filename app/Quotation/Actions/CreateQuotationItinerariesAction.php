<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationItinerary;

class CreateQuotationItinerariesAction
{
    public function __construct(
        protected CreateQuotationItemsAction $itemAction
    ) {}

    public function execute(Quotation $quotation, array $itineraries): void 
    {
        foreach ($itineraries as $itineraryData) {

            $itinerary = QuotationItinerary::create([

                'quotation_id' => $quotation->id,

                'day_number' => $itineraryData['day_number'],

                'travel_date' => $itineraryData['travel_date'] ?? null,

                'title' => $itineraryData['title'] ?? null,

                'description' => $itineraryData['description'] ?? null,

                'sort_order' => $itineraryData['sort_order'] ?? 1,
            ]);

            // Crear items del día
            $this->itemAction->execute(
                $itinerary,
                $itineraryData['items'] ?? []
            );
        }
    }
}