<?php

namespace Tests\Feature\Pricing;

use App\Pricing\Price\DTOs\PriceContext;
use App\Pricing\Price\Exceptions\AmbiguousPriceException;
use App\Pricing\Price\Exceptions\PriceNotFoundException;
use App\Pricing\Price\Models\Price;
use App\Pricing\Price\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PriceResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_resolves_a_permanent_room_price(): void
    {
        $resolved = $this->pricingService()->resolve(
            $this->context('DBL', 'ROOM', quantity: 1)
        );

        $this->assertSame('95.00', $resolved->baseCost);
        $this->assertSame('120.00', $resolved->baseSalePrice);
        $this->assertSame($resolved->baseCost, $resolved->finalCost);
        $this->assertSame($resolved->baseSalePrice, $resolved->finalSalePrice);
        $this->assertNull($resolved->priceListId);
        $this->assertNull($resolved->priceListItemId);
    }

    public function test_it_resolves_a_transport_price_using_passengers_served(): void
    {
        $resolved = $this->pricingService()->resolve(
            $this->context('VAN', 'GROUP', quantity: 6)
        );

        $this->assertSame('120.00', $resolved->baseCost);
        $this->assertSame('150.00', $resolved->baseSalePrice);
    }

    public function test_it_resolves_transport_price_boundaries_by_passengers_served(): void
    {
        $expectedRanges = [
            1 => ['90.00', '120.00'],
            4 => ['90.00', '120.00'],
            5 => ['120.00', '150.00'],
            7 => ['120.00', '150.00'],
            8 => ['145.00', '180.00'],
            10 => ['145.00', '180.00'],
        ];

        foreach ($expectedRanges as $passengerCount => [$cost, $salePrice]) {
            $resolved = $this->pricingService()->resolve(
                $this->context(
                    'VAN',
                    'GROUP',
                    quantity: $passengerCount,
                )
            );

            $this->assertSame(
                $cost,
                $resolved->baseCost,
                "Costo incorrecto para {$passengerCount} pasajeros."
            );

            $this->assertSame(
                $salePrice,
                $resolved->baseSalePrice,
                "Venta incorrecta para {$passengerCount} pasajeros."
            );
        }
    }

    public function test_it_fails_when_transport_quantity_exceeds_variant_price_ranges(): void
    {
        $this->expectException(PriceNotFoundException::class);

        $this->pricingService()->resolve(
            $this->context('VAN', 'GROUP', quantity: 11)
        );
    }

    public function test_it_does_not_resolve_an_inactive_price(): void
    {
        Price::query()
            ->where('service_variant_id', $this->variantId('VAN'))
            ->where('price_type_id', $this->priceTypeId('GROUP'))
            ->where('min_quantity', 5)
            ->where('max_quantity', 7)
            ->update(['active' => false]);

        $this->expectException(PriceNotFoundException::class);

        $this->pricingService()->resolve(
            $this->context('VAN', 'GROUP', quantity: 6)
        );
    }

    public function test_it_prefers_a_higher_priority_seasonal_price(): void
    {
        $base = Price::query()
            ->where('service_variant_id', $this->variantId('DBL'))
            ->where('price_type_id', $this->priceTypeId('ROOM'))
            ->firstOrFail();

        Price::create([
            'service_variant_id' => $base->service_variant_id,
            'price_type_id' => $base->price_type_id,
            'passenger_type_id' => null,
            'currency_id' => $base->currency_id,
            'min_quantity' => null,
            'max_quantity' => null,
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'cost' => 130,
            'sale_price' => 170,
            'priority' => 2,
            'active' => true,
        ]);

        $resolved = $this->pricingService()->resolve(
            $this->context(
                'DBL',
                'ROOM',
                quantity: 1,
                date: '2026-07-15'
            )
        );

        $this->assertSame('130.00', $resolved->baseCost);
        $this->assertSame('170.00', $resolved->baseSalePrice);
    }

    public function test_it_resolves_an_exact_passenger_type(): void
    {
        $resolved = $this->pricingService()->resolve(
            $this->context(
                'ADULT',
                'PASSENGER',
                quantity: 3,
                passengerTypeCode: 'ADT'
            )
        );

        $this->assertSame('120.00', $resolved->baseCost);
        $this->assertSame('150.00', $resolved->baseSalePrice);
    }

    public function test_it_fails_when_no_compatible_price_exists(): void
    {
        $this->expectException(PriceNotFoundException::class);

        $this->pricingService()->resolve(
            new PriceContext(
                serviceVariantId: $this->variantId('DBL'),
                priceTypeId: $this->priceTypeId('ROOM'),
                currencyId: 999999,
                serviceDate: CarbonImmutable::parse('2026-07-15'),
                quantity: 1,
            )
        );
    }

    public function test_it_fails_when_two_prices_have_equal_specificity(): void
    {
        $base = Price::query()
            ->where('service_variant_id', $this->variantId('DBL'))
            ->where('price_type_id', $this->priceTypeId('ROOM'))
            ->firstOrFail();

        Price::create([
            'service_variant_id' => $base->service_variant_id,
            'price_type_id' => $base->price_type_id,
            'passenger_type_id' => null,
            'currency_id' => $base->currency_id,
            'min_quantity' => null,
            'max_quantity' => null,
            'valid_from' => null,
            'valid_to' => null,
            'cost' => 96,
            'sale_price' => 121,
            'priority' => 1,
            'active' => true,
        ]);

        $this->expectException(AmbiguousPriceException::class);

        $this->pricingService()->resolve(
            $this->context('DBL', 'ROOM', quantity: 1)
        );
    }

    private function pricingService(): PricingService
    {
        return app(PricingService::class);
    }

    private function context(
        string $variantCode,
        string $priceTypeCode,
        int $quantity,
        string $date = '2026-06-15',
        ?string $passengerTypeCode = null,
    ): PriceContext {
        return new PriceContext(
            serviceVariantId: $this->variantId($variantCode),
            priceTypeId: $this->priceTypeId($priceTypeCode),
            currencyId: $this->currencyId('USD'),
            serviceDate: CarbonImmutable::parse($date),
            quantity: $quantity,
            passengerTypeId: $passengerTypeCode === null
                ? null
                : $this->passengerTypeId($passengerTypeCode),
        );
    }

    private function variantId(string $code): int
    {
        return (int) DB::table('service_variants')
            ->where('code', $code)
            ->value('id');
    }

    private function priceTypeId(string $code): int
    {
        return (int) DB::table('price_types')
            ->where('code', $code)
            ->value('id');
    }

    private function passengerTypeId(string $code): int
    {
        return (int) DB::table('passenger_types')
            ->where('code', $code)
            ->value('id');
    }

    private function currencyId(string $code): int
    {
        return (int) DB::table('currencies')
            ->where('code', $code)
            ->value('id');
    }
}
