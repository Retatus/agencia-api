<?php

namespace App\Http\Controllers\Pricing;

use App\Filters\Pricing\PriceListFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pricing\PriceList\StorePriceListRequest;
use App\Http\Requests\Pricing\PriceList\UpdatePriceListRequest;
use App\Http\Resources\Pricing\PriceListResource;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceListController extends Controller
{
    private array $relations = [
        'currency',
    ];

    public function index(
        Request $request,
        PriceListFilter $filter
    )
    {
        $items = $filter
            ->apply(
                PriceList::query()->with($this->relations)
            )
            ->paginate(
                $request->integer('per_page', 20)
            );

        return PriceListResource::collection($items);
    }

    public function store(
        StorePriceListRequest $request
    ): PriceListResource {

        $item = PriceList::create(
            $request->validated()
        );

        $item->load($this->relations);

        return new PriceListResource($item);
    }

    public function show(
        PriceList $priceList
    ): PriceListResource {

        $priceList->load($this->relations);

        return new PriceListResource($priceList);
    }

    public function update(
        UpdatePriceListRequest $request,
        PriceList $priceList
    ): PriceListResource {

        $priceList->update(
            $request->validated()
        );

        $priceList->load($this->relations);

        return new PriceListResource($priceList);
    }

    public function destroy(
        PriceList $priceList
    )
    {
        if ($priceList->prices()->exists()) {

            return response()->json([

                'message' => 'No puede eliminarse porque tiene precios asociados.'

            ], Response::HTTP_CONFLICT);

        }

        $priceList->delete();

        return response()->json([

            'message' => 'Registro eliminado correctamente.'

        ]);
    }
}