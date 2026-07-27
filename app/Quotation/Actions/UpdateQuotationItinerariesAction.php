<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationItinerary;

class UpdateQuotationItinerariesAction
{
    public function execute(
        Quotation $quotation,
        array $itineraries
    ): void {

        $receivedItineraryIds = collect($itineraries)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Eliminar itinerarios que ya no existen
        |--------------------------------------------------------------------------
        */

        $quotation->itineraries()
            ->when(
                !empty($receivedItineraryIds),
                fn ($query) =>
                    $query->whereNotIn('id', $receivedItineraryIds)
            )
            ->when(
                empty($receivedItineraryIds),
                fn ($query) => $query
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Crear / actualizar itinerarios
        |--------------------------------------------------------------------------
        */

        foreach ($itineraries as $itineraryData) {

            $items = $itineraryData['items'] ?? [];

            $id = $itineraryData['id'] ?? null;

            unset(
                $itineraryData['id'],
                //$itineraryData['uuid'],
                $itineraryData['quotation_id'],
                $itineraryData['items']
            );

            if ($id) {

                $itinerary = $quotation
                    ->itineraries()
                    ->where('id', $id)
                    ->first();

                if (!$itinerary) {
                    continue;
                }

                $itinerary->update(
                    $itineraryData
                );

            } else {

                $itinerary = $quotation
                    ->itineraries()
                    ->create(
                        $itineraryData
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Sincronizar Items
            |--------------------------------------------------------------------------
            */

            $this->syncItems(
                $itinerary,
                $items
            );
        }
    }

    protected function syncItems(
        QuotationItinerary $itinerary,
        array $items
    ): void {

        $receivedItemIds = collect($items)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Eliminar items que ya no existen
        |--------------------------------------------------------------------------
        */

        $itinerary->items()
            ->when(
                !empty($receivedItemIds),
                fn ($query) =>
                    $query->whereNotIn('id', $receivedItemIds)
            )
            ->when(
                empty($receivedItemIds),
                fn ($query) => $query
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Crear / actualizar items
        |--------------------------------------------------------------------------
        */

        foreach ($items as $itemData) {

            $id = $itemData['id'] ?? null;

            unset(
                $itemData['id'],
                //$itemData['uuid'],
                $itemData['quotation_itinerary_id']
            );

            if ($id) {

                $item = $itinerary
                    ->items()
                    ->where('id', $id)
                    ->first();

                if (!$item) {
                    continue;
                }

                $item->update(
                    $itemData
                );

            } else {

                $itinerary->items()->create(
                    $itemData
                );
            }
        }
    }
}