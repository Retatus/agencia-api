<?php

namespace App\Quotation\Actions;

use App\Quotation\Models\Quotation;

class ResolveQuotationCalculationStateAction
{
    public function execute(Quotation $quotation, array $data): array
    {
        $quotation->loadMissing(['passengers', 'itineraries.items']);

        $oldPassengerSignature = $quotation->passengers
            ->map(fn ($passenger) => [
                (int) $passenger->id,
                (int) $passenger->passenger_type_id,
                (bool) $passenger->active,
            ])
            ->sortBy(fn ($value) => $value[0])
            ->values()
            ->all();

        $newPassengerSignature = collect($data['passengers'] ?? [])
            ->map(fn ($passenger) => [
                (int) ($passenger['id'] ?? 0),
                (int) $passenger['passenger_type_id'],
                (bool) ($passenger['active'] ?? true),
            ])
            ->sortBy(fn ($value) => $value[0])
            ->values()
            ->all();

        $oldItems = [];
        $oldDates = [];

        foreach ($quotation->itineraries as $itinerary) {
            $itineraryKey = (string) $itinerary->id;
            $oldDates[$itineraryKey] = $itinerary->travel_date?->format('Y-m-d');

            foreach ($itinerary->items as $item) {
                if ($item->item_type !== 'CATALOG') {
                    continue;
                }

                $key = $item->group_uuid ?: $item->uuid;
                $oldItems[$key] = max(
                    $oldItems[$key] ?? 0,
                    $item->calculated_at?->getTimestamp() ?? 0,
                );
            }
        }

        $newItems = [];
        $newDates = [];
        $keysByItinerary = [];

        foreach ($data['itineraries'] ?? [] as $itinerary) {
            $itineraryKey = (string) ($itinerary['id'] ?? $itinerary['uuid']);
            $newDates[$itineraryKey] = $itinerary['travel_date'] ?? null;

            foreach ($itinerary['items'] ?? [] as $item) {
                if (($item['item_type'] ?? null) !== 'CATALOG') {
                    continue;
                }

                $key = $item['group_uuid'] ?? $item['uuid'] ?? null;

                if (!$key) {
                    continue;
                }

                $timestamp = isset($item['calculated_at'])
                    ? (strtotime($item['calculated_at']) ?: 0)
                    : 0;

                $newItems[$key] = max($newItems[$key] ?? 0, $timestamp);
                $keysByItinerary[$itineraryKey][$key] = true;
            }
        }

        $currentKeys = array_keys($newItems);
        $pending = array_values(array_intersect(
            array_unique([
                ...($quotation->pending_calculation_items ?? []),
                ...($data['pending_calculation_items'] ?? []),
            ]),
            $currentKeys,
        ));

        $recalculated = [];

        foreach ($newItems as $key => $timestamp) {
            if ($timestamp > ($oldItems[$key] ?? 0)) {
                $recalculated[$key] = true;
            }
        }

        $pending = array_values(array_filter(
            $pending,
            fn ($key) => !isset($recalculated[$key]),
        ));

        $reasons = $data['calculation_dirty_reasons']
            ?? $quotation->calculation_dirty_reasons
            ?? [];

        $allKeysAffected =
            $oldPassengerSignature !== $newPassengerSignature
            || (int) $quotation->currency_id !== (int) ($data['currency_id'] ?? 0)
            || $quotation->travel_date?->format('Y-m-d') !== ($data['travel_date'] ?? null);

        $affectedKeys = $allKeysAffected ? $currentKeys : [];

        if ($oldPassengerSignature !== $newPassengerSignature) {
            $reasons[] = 'PASSENGER_PRICING_DATA_CHANGED';
        }

        if ((int) $quotation->currency_id !== (int) ($data['currency_id'] ?? 0)) {
            $reasons[] = 'CURRENCY_CHANGED';
        }

        if ($quotation->travel_date?->format('Y-m-d') !== ($data['travel_date'] ?? null)) {
            $reasons[] = 'TRAVEL_DATE_CHANGED';
        }

        foreach ($newDates as $itineraryKey => $travelDate) {
            if (array_key_exists($itineraryKey, $oldDates) && $oldDates[$itineraryKey] !== $travelDate) {
                $affectedKeys = [
                    ...$affectedKeys,
                    ...array_keys($keysByItinerary[$itineraryKey] ?? []),
                ];
                $reasons[] = 'ITINERARY_DATE_CHANGED';
            }
        }

        foreach (array_unique($affectedKeys) as $key) {
            if (!isset($recalculated[$key])) {
                $pending[] = $key;
            }
        }

        $pending = array_values(array_unique($pending));

        $data['pending_calculation_items'] = $pending;
        $data['calculation_status'] = empty($pending)
            ? Quotation::CALCULATION_CURRENT
            : Quotation::CALCULATION_DIRTY;
        $data['calculation_dirty_reasons'] = empty($pending)
            ? []
            : array_values(array_unique($reasons));
        $data['calculated_at'] = empty($pending) ? now()->toISOString() : null;

        return $data;
    }
}
