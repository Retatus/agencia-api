<?php

namespace App\Pricing\PriceListItem\Controllers;

use App\Http\Controllers\Controller;
use App\Pricing\PriceListItem\Actions\CreatePriceListItemAction;
use App\Pricing\PriceListItem\Actions\UpdatePriceListItemAction;
use App\Pricing\PriceListItem\Models\PriceListItem;
use App\Pricing\PriceListItem\Requests\FilterPriceListItemRequest;
use App\Pricing\PriceListItem\Requests\StorePriceListItemRequest;
use App\Pricing\PriceListItem\Requests\UpdatePriceListItemRequest;
use App\Pricing\PriceListItem\Resources\PriceListItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PriceListItemController extends Controller
{
    private array $relations = [
        'priceList.currency',
        'price.serviceVariant.service',
        'price.priceType',
        'price.passengerType',
        'price.currency',
    ];

    public function index(
        FilterPriceListItemRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();

        $items = PriceListItem::query()
            ->with($this->relations)
            ->when(
                isset($filters['price_list_id']),
                fn ($query) => $query->where('price_list_id', $filters['price_list_id'])
            )
            ->when(
                isset($filters['price_id']),
                fn ($query) => $query->where('price_id', $filters['price_id'])
            )
            ->when(
                array_key_exists('active', $filters),
                fn ($query) => $query->where('active', $filters['active'])
            )
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 20);

        return PriceListItemResource::collection($items);
    }

    public function store(
        StorePriceListItemRequest $request,
        CreatePriceListItemAction $action,
    ): PriceListItemResource {
        $item = $action->execute($request->validated());

        return new PriceListItemResource($item->load($this->relations));
    }

    public function show(PriceListItem $priceListItem): PriceListItemResource
    {
        return new PriceListItemResource(
            $priceListItem->load($this->relations)
        );
    }

    public function update(
        UpdatePriceListItemRequest $request,
        PriceListItem $priceListItem,
        UpdatePriceListItemAction $action,
    ): PriceListItemResource {
        $item = $action->execute($priceListItem, $request->validated());

        return new PriceListItemResource($item->load($this->relations));
    }

    public function destroy(PriceListItem $priceListItem): Response
    {
        $priceListItem->delete();

        return response()->noContent();
    }
}
