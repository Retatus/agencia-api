<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;

class CreateQuotationPassengersAction
{
    /**
     * Crear pasajeros de la cotización.
     */
    public function execute(Quotation $quotation, array $passengers = []): void {

        if (empty($passengers)) {
            return;
        }

        foreach ($passengers as $passenger) {

            $quotation->passengers()->create([

                'passenger_type_id' => $passenger['passenger_type_id'],

                'first_name' => $passenger['first_name'],

                'last_name' => $passenger['last_name'],

                'birth_date' => $passenger['birth_date'] ?? null,

                'document_number' => $passenger['document_number'] ?? null,

                'nationality' => $passenger['nationality'] ?? null,

                'email' => $passenger['email'] ?? null,

                'phone' => $passenger['phone'] ?? null,

                'active' => $passenger['active'] ?? true,
            ]);
        }
    }
}