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
                $pricingChanged = false;

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

                    if (
                        (array_key_exists('passenger_type_id', $values)
                            && (int) $passenger->passenger_type_id !== (int) $values['passenger_type_id'])
                        || (array_key_exists('active', $values)
                            && (bool) $passenger->active !== (bool) $values['active'])
                    ) {
                        $pricingChanged = true;
                    }

                    $passenger->update(
                        $values
                    );

                    $updated[] =
                        $passenger->fresh();
                }

                if ($pricingChanged) {
                    $quotation->markCalculationDirty('PASSENGER_PRICING_DATA_CHANGED');
                }

                return $updated;
            }
        );
    }
}
