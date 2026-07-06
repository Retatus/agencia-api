<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationPassenger;

class CreateQuotationPassengersAction
{
    /**
     * Crear los pasajeros de una cotización.
     */
    public function execute(Quotation $quotation, array $passengers): void {

        foreach ($passengers as $passenger) {

            QuotationPassenger::create([

                'quotation_id' => $quotation->id,

                'passenger_type_id' => $passenger['passenger_type_id'],

                'first_name' => $passenger['first_name'],

                'last_name' => $passenger['last_name'],

                'birth_date' => $passenger['birth_date'] ?? null,

                'document_number' => $passenger['document_number'] ?? null,

                'nationality' => $passenger['nationality'] ?? null,

                'email' => $passenger['email'] ?? null,

                'phone' => $passenger['phone'] ?? null,

                'active' => true,
            ]);
        }
    }
}