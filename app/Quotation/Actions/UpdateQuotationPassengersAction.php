<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationPassenger;

class UpdateQuotationPassengersAction
{
    public function execute(
        Quotation $quotation,
        array $passengers
    ): void {

        $receivedIds = collect($passengers)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Eliminar pasajeros que ya no existen en la cotización
        |--------------------------------------------------------------------------
        */

        $quotation->passengers()
            ->when(
                !empty($receivedIds),
                fn ($query) => $query->whereNotIn('id', $receivedIds)
            )
            ->when(
                empty($receivedIds),
                fn ($query) => $query
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Crear / actualizar pasajeros
        |--------------------------------------------------------------------------
        */

        foreach ($passengers as $passengerData) {

            $id = $passengerData['id'] ?? null;

            unset(
                $passengerData['id'],
                $passengerData['uuid'],
                $passengerData['quotation_id']
            );

            if ($id) {

                $passenger = $quotation
                    ->passengers()
                    ->where('id', $id)
                    ->first();

                if (!$passenger) {
                    continue;
                }

                $passenger->update($passengerData);

            } else {

                $quotation->passengers()->create(
                    $passengerData
                );
            }
        }
    }
}