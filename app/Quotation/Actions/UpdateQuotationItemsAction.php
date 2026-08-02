<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\QuotationItinerary;

class UpdateQuotationItemsAction
{
    public function execute(
        QuotationItinerary $itinerary,
        array $items
    ): void {

        /*
        |--------------------------------------------------------------------------
        | IDs recibidos
        |--------------------------------------------------------------------------
        */

        $receivedItemIds = collect($items)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Buscar items eliminados
        |--------------------------------------------------------------------------
        */

        $itemsToDelete = $itinerary
            ->items()
            ->when(
                !empty($receivedItemIds),

                fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $receivedItemIds
                    )
            )
            ->when(
                empty($receivedItemIds),

                fn ($query) =>
                    $query
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SoftDelete individual
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | No utilizar:
        |
        | $query->delete()
        |
        | porque no dispara eventos Eloquent.
        |
        | Utilizamos:
        |
        | $item->delete()
        |
        | para que HasHistory registre deleted.
        |
        */

        foreach ($itemsToDelete as $item) {

            $item->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | Crear / actualizar items
        |--------------------------------------------------------------------------
        */

        foreach ($items as $itemData) {

            /*
            |--------------------------------------------------------------------------
            | ID
            |--------------------------------------------------------------------------
            */

            $id = $itemData['id'] ?? null;


            /*
            |--------------------------------------------------------------------------
            | Limpiar datos
            |--------------------------------------------------------------------------
            */

            unset(
                //$itemData['id'],
                $itemData['uuid'],
                $itemData['quotation_itinerary_id']
            );


            /*
            |--------------------------------------------------------------------------
            | Actualizar item existente
            |--------------------------------------------------------------------------
            */

            if ($id) {

                $item = $itinerary
                    ->items()
                    ->where('id', $id)
                    ->first();

                /*
                | El item no pertenece al itinerario.
                */

                if (!$item) {
                    continue;
                }

                $item->update(
                    $itemData
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Crear nuevo item
            |--------------------------------------------------------------------------
            */

            $itinerary
                ->items()
                ->create(
                    $itemData
                );
        }
    }
}