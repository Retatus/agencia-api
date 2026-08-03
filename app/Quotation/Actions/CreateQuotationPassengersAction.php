<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;

class CreateQuotationPassengersAction
{
    /**
     * Crear los pasajeros de una cotización.
     *
     * Los pasajeros se crean mediante la relación
     * de la cotización para garantizar que el
     * quotation_id sea asignado automáticamente.
     *
     * El UUID definitivo, si existe en el modelo,
     * debe ser generado automáticamente por Laravel.
     *
     * El registro de auditoría será realizado
     * automáticamente por HasHistory.
     */
    public function execute(
        Quotation $quotation,
        array $passengers = []
    ): void {

        if (empty($passengers)) {
            return;
        }

        foreach ($passengers as $passengerData) {

            $quotation->passengers()->create([

                'passenger_type_id' => $passengerData['passenger_type_id'],

                'first_name' => $passengerData['first_name'],

                'last_name' => $passengerData['last_name'],

                'birth_date' => $passengerData['birth_date'] ?? null,

                'document_number' => $passengerData['document_number'] ?? null,

                'nationality' => $passengerData['nationality'] ?? null,

                'email' => $passengerData['email'] ?? null,

                'phone' => $passengerData['phone'] ?? null,

                'active' => $passengerData['active'] ?? true,
            ]);
        }
    }
}