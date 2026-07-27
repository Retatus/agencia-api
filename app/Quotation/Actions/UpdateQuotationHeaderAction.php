<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use App\Quotation\Models\QuotationStatus;
use Illuminate\Support\Facades\Auth;

class UpdateQuotationHeaderAction
{
    public function execute(
        Quotation $quotation,
        array $data
    ): Quotation {
        $fields = [
            'customer_id',
            'price_list_id',
            'currency_id',
            'quotation_status_id',
            'exchange_rate',
            'travel_date',
            'valid_until',
            'notes',
            'discount',
            'tax',
            'active',
        ];

        $quotation->fill(
            array_intersect_key(
                $data,
                array_flip($fields)
            )
        );

        $quotation->save();

        return $quotation;
    }
}