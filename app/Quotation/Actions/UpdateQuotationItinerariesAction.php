<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationItinerary;

class UpdateQuotationItinerariesAction
{
    public function __construct(
        protected UpdateQuotationItemsAction $itemAction
    ) {
    }

    /**
     * Actualizar los itinerarios de una cotización.
     *
     * Estrategia:
     *
     * 1. Itinerario existente:
     *    UPDATE sobre el mismo registro.
     *
     * 2. Itinerario nuevo:
     *    INSERT.
     *
     * 3. Itinerario eliminado del frontend:
     *    SOFT DELETE.
     *
     * 4. Los items se sincronizan mediante
     *    UpdateQuotationItemsAction.
     *
     * 5. Nunca se elimina y recrea un itinerario
     *    existente para simular una actualización.
     */
    public function execute(
        Quotation $quotation,
        array $itineraries
    ): void {

        /*
        |--------------------------------------------------------------------------
        | 1. Obtener IDs de itinerarios recibidos
        |--------------------------------------------------------------------------
        |
        | Solo los registros existentes tendrán ID.
        |
        | Los nuevos registros tendrán:
        |
        | id = null
        |
        */

        $receivedItineraryIds = collect($itineraries)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | 2. Buscar itinerarios eliminados
        |--------------------------------------------------------------------------
        |
        | Si un itinerario existe en BD pero ya no viene
        | desde el frontend, significa que fue eliminado.
        |
        | Se realiza SoftDelete.
        |
        */

        $itinerariesToDelete = $quotation
            ->itineraries()
            ->when(
                !empty($receivedItineraryIds),
                fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $receivedItineraryIds
                    )
            )
            ->when(
                empty($receivedItineraryIds),
                fn ($query) =>
                    $query
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. Eliminar lógicamente itinerarios
        |--------------------------------------------------------------------------
        |
        | Antes de eliminar el itinerario:
        |
        | 1. Se eliminan lógicamente sus items.
        | 2. Cada item dispara HasHistory.
        | 3. Se elimina lógicamente el itinerario.
        | 4. El itinerario dispara HasHistory.
        |
        */

        foreach ($itinerariesToDelete as $itinerary) {

            /*
            |--------------------------------------------------------------------------
            | Eliminar items del itinerario
            |--------------------------------------------------------------------------
            */

            $itinerary
                ->items()
                ->get()
                ->each(function ($item) {

                    $item->delete();
                });


            /*
            |--------------------------------------------------------------------------
            | Eliminar itinerario
            |--------------------------------------------------------------------------
            */

            $itinerary->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Crear / actualizar itinerarios
        |--------------------------------------------------------------------------
        */

        foreach ($itineraries as $itineraryData) {

            /*
            |--------------------------------------------------------------------------
            | Extraer items
            |--------------------------------------------------------------------------
            */

            $items = $itineraryData['items'] ?? [];


            /*
            |--------------------------------------------------------------------------
            | Identificador del itinerario
            |--------------------------------------------------------------------------
            */

            $id = $itineraryData['id'] ?? null;


            /*
            |--------------------------------------------------------------------------
            | Datos persistentes del itinerario
            |--------------------------------------------------------------------------
            |
            | No permitimos modificar:
            |
            | - id
            | - uuid
            | - quotation_id
            |
            | Estos valores pertenecen a la identidad
            | y relación de la entidad.
            |
            */

            $data = collect($itineraryData)
                ->only([
                    'day_number',
                    'travel_date',
                    'title',
                    'description',
                    'sort_order',
                    'subtotal',
                ])
                ->toArray();


            /*
            |--------------------------------------------------------------------------
            | 5. Actualizar itinerario existente
            |--------------------------------------------------------------------------
            */

            if ($id) {

                $itinerary = $quotation
                    ->itineraries()
                    ->where('id', $id)
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Seguridad
                |--------------------------------------------------------------------------
                |
                | El ID recibido debe pertenecer a esta cotización.
                |
                | Nunca debemos actualizar un itinerario
                | perteneciente a otra cotización.
                |
                */

                if (!$itinerary) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                |
                | Se mantiene:
                |
                | id
                | uuid
                | created_at
                |
                | Solo se actualizan los campos modificables.
                |
                | HasHistory registra automáticamente:
                |
                | old_value
                | new_value
                | field
                | action = updated
                |
                */

                $itinerary->update(
                    $data
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 6. Crear nuevo itinerario
            |--------------------------------------------------------------------------
            |
            | Solo se ejecuta cuando el frontend envía
            | un itinerario sin ID.
            |
            | Laravel genera el UUID automáticamente
            | si el modelo utiliza HasUuids.
            |
            */

            else {

                $itinerary = $quotation
                    ->itineraries()
                    ->create(
                        $data
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | 7. Sincronizar items
            |--------------------------------------------------------------------------
            |
            | La responsabilidad de los items está
            | completamente delegada a:
            |
            | UpdateQuotationItemsAction
            |
            */

            $this->itemAction->execute(
                $itinerary,
                $items
            );
        }
    }
}