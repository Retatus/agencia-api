<?php

namespace App\Quotation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationStatusResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'active' => (bool) $this->active,
        ];
    }
}