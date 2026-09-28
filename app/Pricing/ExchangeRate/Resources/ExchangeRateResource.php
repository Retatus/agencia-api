<?php

namespace App\Pricing\ExchangeRate\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_currency_id' => $this->from_currency_id,
            'to_currency_id' => $this->to_currency_id,
            'from_currency' => $this->whenLoaded('fromCurrency'),
            'to_currency' => $this->whenLoaded('toCurrency'),
            'rate' => $this->rate,
            'effective_date' => $this->effective_date?->toDateString(),
            'source' => $this->source,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
