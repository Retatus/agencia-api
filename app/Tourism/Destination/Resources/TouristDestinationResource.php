<?php

namespace App\Tourism\Destination\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TouristDestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency'),
            'duration_days' => $this->duration_days,
            'active' => $this->active,
            'estimated_cost' => $this->when(
                $this->relationLoaded('days'),
                fn () => $this->days->flatMap->items->sum(
                    fn ($item) => (float) $item->estimated_cost * (float) $item->quantity
                )
            ),
            'estimated_price' => $this->when(
                $this->relationLoaded('days'),
                fn () => $this->days->flatMap->items->sum(
                    fn ($item) => (float) $item->estimated_price * (float) $item->quantity
                )
            ),
            'days' => $this->whenLoaded('days', fn () => $this->days->map(fn ($day) => [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'title' => $day->title,
                'description' => $day->description,
                'sort_order' => $day->sort_order,
                'items' => $day->relationLoaded('items')
                    ? $day->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'duration' => $item->duration,
                        'quantity' => $item->quantity,
                        'estimated_cost' => $item->estimated_cost,
                        'estimated_price' => $item->estimated_price,
                        'sort_order' => $item->sort_order,
                        'active' => $item->active,
                    ])->values()
                    : [],
            ])->values()),
        ];
    }
}
