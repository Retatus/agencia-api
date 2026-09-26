<?php

namespace Tests\Feature\Pricing;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Models\Price;
use App\Pricing\Price\Services\PricingService;
use App\Pricing\PriceList\Exceptions\InvalidPriceListException;
use App\Pricing\PriceList\Models\PriceList;
use App\Pricing\PriceListItem\Models\PriceListItem;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PriceListAdjustmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_without_a_commercial_policy_it_returns_the_base_price(): void
    {
        $resolved = $this->resolve();

        $this->assertSame('95.00', $resolved->finalCost);
        $this->assertSame('120.00', $resolved->finalSalePrice);
        $this->assertNull($resolved->priceListId);
    }

    public function test_it_applies_independent_percentage_adjustments(): void
    {
        $list = $this->priceList();
        $item = $this->addItem($list, 'PERCENTAGE', 10, 20);

        $resolved = $this->resolve((int) $list->getKey());

        $this->assertSame('104.50', $resolved->finalCost);
        $this->assertSame('144.00', $resolved->finalSalePrice);
        $this->assertSame($list->getKey(), $resolved->priceListId);
        $this->assertSame($item->getKey(), $resolved->priceListItemId);
        $this->assertSame('PERCENTAGE', $resolved->adjustmentType);
    }

    public function test_it_applies_a_fixed_adjustment(): void
    {
        $list = $this->priceList();
        $this->addItem($list, 'FIXED', 5, -10);

        $resolved = $this->resolve((int) $list->getKey());

        $this->assertSame('100.00', $resolved->finalCost);
        $this->assertSame('110.00', $resolved->finalSalePrice);
    }

    public function test_it_overrides_the_amounts(): void
    {
        $list = $this->priceList();
        $this->addItem($list, 'OVERRIDE', 80, 105);

        $resolved = $this->resolve((int) $list->getKey());

        $this->assertSame('80.00', $resolved->finalCost);
        $this->assertSame('105.00', $resolved->finalSalePrice);
    }

    public function test_a_list_without_an_item_for_the_price_uses_the_base_price(): void
    {
        $list = $this->priceList();
        $resolved = $this->resolve((int) $list->getKey());

        $this->assertSame('95.00', $resolved->finalCost);
        $this->assertSame('120.00', $resolved->finalSalePrice);
        $this->assertNull($resolved->priceListId);
    }

    public function test_an_expired_selected_list_is_rejected(): void
    {
        $list = $this->priceList([
            'valid_from' => '2025-01-01',
            'valid_to' => '2025-12-31',
        ]);

        $this->expectException(InvalidPriceListException::class);

        $this->resolve((int) $list->getKey());
    }

    private function resolve(?int $commercialPolicyId = null)
    {
        return app(PricingService::class)->resolve(new PriceContext(
            serviceVariantId: $this->variantId('DBL'),
            priceTypeId: $this->priceTypeId('ROOM'),
            currencyId: $this->currencyId('USD'),
            serviceDate: CarbonImmutable::parse('2026-06-15'),
            quantity: 1,
            commercialPolicyId: $commercialPolicyId,
        ));
    }

    private function priceList(array $overrides = []): PriceList
    {
        return PriceList::query()->create(array_merge([
            'code' => 'TEST-POLICY',
            'name' => 'Política de prueba',
            'description' => 'Lista para pruebas automatizadas.',
            'currency_id' => $this->currencyId('USD'),
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'priority' => 10,
            'is_default' => false,
            'active' => true,
        ], $overrides));
    }

    private function addItem(
        PriceList $list,
        string $type,
        int|float|null $cost,
        int|float|null $sale,
    ): PriceListItem {
        return PriceListItem::query()->create([
            'price_list_id' => $list->getKey(),
            'price_id' => $this->basePrice()->getKey(),
            'adjustment_type' => $type,
            'cost_adjustment' => $cost,
            'sale_adjustment' => $sale,
            'active' => true,
        ]);
    }

    private function basePrice(): Price
    {
        return Price::query()
            ->where('service_variant_id', $this->variantId('DBL'))
            ->where('price_type_id', $this->priceTypeId('ROOM'))
            ->firstOrFail();
    }

    private function variantId(string $code): int
    {
        return (int) DB::table('service_variants')->where('code', $code)->value('id');
    }

    private function priceTypeId(string $code): int
    {
        return (int) DB::table('price_types')->where('code', $code)->value('id');
    }

    private function currencyId(string $code): int
    {
        return (int) DB::table('currencies')->where('code', $code)->value('id');
    }
}
