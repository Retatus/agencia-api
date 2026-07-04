<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'decimals' => $this->decimals,
            'is_base' => $this->is_base,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
