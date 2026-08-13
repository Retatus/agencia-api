<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class GenerateQuotationPassengersAction
{
    public function execute(
        Quotation $quotation,
        array $data
    ): array {
        return DB::transaction(
            function () use (
                $quotation,
                $data
            ) {
                $created = [];

                $sortOrder = (int) (
                    $quotation
                        ->passengers()
                        ->max('sort_order')
                    ?? 0
                );

                foreach (
                    $data['groups']
                    as $group
                ) {
                    for (
                        $index = 1;
                        $index <= $group['quantity'];
                        $index++
                    ) {
                        $sortOrder++;

                        $passenger =
                            $quotation
                                ->passengers()
                                ->create([
                                    'passenger_type_id' =>
                                        $group[
                                            'passenger_type_id'
                                        ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Placeholder
                                    |--------------------------------------------------------------------------
                                    */

                                    'first_name' =>
                                        'Pendiente',

                                    'last_name' =>
                                        str_pad(
                                            (string) $sortOrder,
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        ),

                                    'nationality' =>
                                        $data[
                                            'nationality'
                                        ] ?? null,

                                    'active' =>
                                        true,

                                    'sort_order' =>
                                        $sortOrder,
                                ]);

                        $created[] =
                            $passenger;
                    }
                }

                return $created;
            }
        );
    }
}