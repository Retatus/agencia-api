<?php

namespace App\Quotation\Http\Resources;

use App\Quotation\Services\QuotationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'allowed_actions' => app(QuotationWorkflowService::class)
                ->allowedActions($this->resource),
        ];
    }
}
