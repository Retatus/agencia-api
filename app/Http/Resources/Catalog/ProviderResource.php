<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'business_name' => $this->business_name,
            'commercial_name' => $this->commercial_name,
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->whenLoaded('documentType'),
            'document_number' => $this->document_number,
            'tax_name' => $this->tax_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'notes' => $this->notes,
            'active' => $this->active,
        ];
    }
}
