<?php

namespace App\Pricing\PriceList\Controllers;

use App\Filters\Pricing\PriceListFilter;
use App\Http\Controllers\Controller;
use App\Pricing\PriceList\Requests\StorePriceListRequest;
use App\Pricing\PriceList\Requests\UpdatePriceListRequest;
use App\Pricing\PriceList\Resources\PriceListResource;
use App\Pricing\PriceList\Models\PriceList;
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