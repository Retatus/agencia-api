<?php

namespace App\Quotation\Calculation\Services;

final class PassengerDistributionService
{
    /**
     * Distribuir pasajeros de forma equilibrada entre las unidades físicas.
     *
     * @return array<int, array<string, int>>|null
     */
    public function distribute(
        array $vehicleAllocations,
        int $passengerCount,
    ): ?array {
        $units = [];

        foreach ($vehicleAllocations as $allocationIndex => $allocation) {
            for ($unitIndex = 0; $unitIndex < (int) $allocation['quantity']; $unitIndex++) {
                $units[] = [
                    'allocation_index' => $allocationIndex,
                    'unit_index' => $unitIndex + 1,
                    'min_capacity' => (int) ($allocation['minimum_capacity_per_vehicle'] ?? 1),
                    'max_capacity' => (int) $allocation['capacity_per_vehicle'],
                    'passenger_count' => (int) ($allocation['minimum_capacity_per_vehicle'] ?? 1),
                ];
            }
        }

        if ($units === []) {
            return null;
        }

        $assigned = array_sum(array_column($units, 'passenger_count'));

        if ($assigned > $passengerCount) {
            return null;
        }

        $remaining = $passengerCount - $assigned;

        while ($remaining > 0) {
            $eligible = array_filter(
                array_keys($units),
                fn (int $index): bool =>
                    $units[$index]['passenger_count'] < $units[$index]['max_capacity'],
            );

            if ($eligible === []) {
                return null;
            }

            usort($eligible, function (int $left, int $right) use ($units): int {
                $leftRatio = $units[$left]['passenger_count'] / $units[$left]['max_capacity'];
                $rightRatio = $units[$right]['passenger_count'] / $units[$right]['max_capacity'];

                if ($leftRatio !== $rightRatio) {
                    return $leftRatio <=> $rightRatio;
                }

                return $units[$right]['max_capacity'] <=> $units[$left]['max_capacity'];
            });

            $units[$eligible[0]]['passenger_count']++;
            $remaining--;
        }

        return $units;
    }
}