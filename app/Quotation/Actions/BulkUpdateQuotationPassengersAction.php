<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;
use Illuminate\Support\Facades\DB;

class BulkUpdateQuotationPassengersAction
{
    public function execute(
        Quotation $quotation,
        array $passengers
    ): array {
        return DB::transaction(
            function () use (
                $quotation,
                $passengers
            ) {
                $updated = [];

                foreach (
                    $passengers
                    as $data
                ) {
                    $passenger =
                        $quotation
                            ->passengers()
                            ->where(
                                'uuid',
                                $data['uuid']
                            )
                            ->firstOrFail();

                    $values =
                        collect($data)
                            ->only([
                                'passenger_type_id',
                                'nationality',
                                'active',
                            ])
                            ->toArray();

                    $passenger->update(
                        $values
                    );

                    $updated[] =
                        $passenger->fresh();
                }

                return $updated;
            }
        );
    }
}