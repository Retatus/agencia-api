<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Quotation\Http\Resources\QuotationStatusResource;
use App\Quotation\Models\QuotationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuotationStatusController extends Controller
{
    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $query = QuotationStatus::query();

        if ($request->has('active')) {
            $query->where(
                'active',
                $request->boolean('active')
            );
        }

        $statuses = $query
            ->orderBy('id')
            ->get();

        return QuotationStatusResource::collection(
            $statuses
        );
    }
}