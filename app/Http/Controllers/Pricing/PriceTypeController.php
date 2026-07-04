<?php

namespace App\Http\Controllers\Pricing;

use App\Filters\Pricing\PriceTypeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pricing\PriceType\StorePriceTypeRequest;
use App\Http\Requests\Pricing\PriceType\UpdatePriceTypeRequest;
use App\Http\Resources\Pricing\PriceTypeResource;
use App\Models\PriceType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceTypeController extends Controller
{
    public function index(
        Request $request,
        PriceTypeFilter $filter
    )
    {
        $items = $filter
            ->apply(PriceType::query())
            ->paginate(
                $request->integer('per_page', 20)
            );

        return PriceTypeResource::collection($items);
    }

    public function store(
        StorePriceTypeRequest $request
    ): PriceTypeResource {

        $item = PriceType::create(
            $request->validated()
        );

        return new PriceTypeResource($item);
    }

    public function show(
        PriceType $priceType
    ): PriceTypeResource {

        return new PriceTypeResource($priceType);
    }

    public function update(
        UpdatePriceTypeRequest $request,
        PriceType $priceType
    ): PriceTypeResource {

        $priceType->update(
            $request->validated()
        );

        return new PriceTypeResource($priceType);
    }

    public function destroy(
        PriceType $priceType
    )
    {
        if ($priceType->prices()->exists()) {

            return response()->json([

                'message' => 'No puede eliminarse porque tiene precios asociados.'

            ], Response::HTTP_CONFLICT);

        }

        $priceType->delete();

        return response()->json([
            'message' => 'Registro eliminado correctamente.'
        ]);
    }
}