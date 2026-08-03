<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationItinerary;

class CreateQuotationItinerariesAction
{
    public function __construct(
        protected CreateQuotationItemsAction $itemAction
    ) {
    }

    /**
     * Crear los itinerarios de una cotización.
     *
     * Todos los itinerarios creados durante esta operación
     * compartirán automáticamente el mismo batch_uuid
     * generado por CreateQuotationAction.
     */
    public function execute(
        Quotation $quotation,
        array $itineraries
    ): void {

        foreach ($itineraries as $itineraryData) {

            /*
            |--------------------------------------------------------------------------
            | Datos del itinerario
            |--------------------------------------------------------------------------
            |
            | No utilizamos updateOrCreate().
            |
            | Este Action se ejecuta exclusivamente durante la creación
            | de una cotización, por lo tanto todos los itinerarios
            | deben ser nuevos.
            |
            */

            $itinerary = $quotation
                ->itineraries()
                ->create([
                    'quotation_id' => $quotation->id,

                    'day_number' => $itineraryData['day_number'],

                    'travel_date' => $itineraryData['travel_date'] ?? null,

                    'title' => $itineraryData['title'] ?? null,

                    'description' => $itineraryData['description'] ?? null,

                    'sort_order' => $itineraryData['sort_order'] ?? 1,
                ]);


            /*
            |--------------------------------------------------------------------------
            | Crear items del itinerario
            |--------------------------------------------------------------------------
            |
            | CreateQuotationItemsAction se encargará de crear
            | todos los items asociados al itinerario.
            |
            | Como utiliza Eloquent ->create(), HasHistory
            | registrará automáticamente cada item como "created".
            |
            */

            $this->itemAction->execute(
                $itinerary,
                $itineraryData['items'] ?? []
            );
        }
    }
}