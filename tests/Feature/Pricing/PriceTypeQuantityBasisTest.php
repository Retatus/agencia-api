<?php

namespace Tests\Feature\Pricing;

use App\Pricing\PriceType\Enums\QuantityBasis;
use App\Pricing\PriceType\Models\PriceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceTypeQuantityBasisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_group_and_passenger_prices_use_passengers_as_quantity_basis(): void
    {
        $this->assertSame(
            QuantityBasis::PASSENGERS,
            PriceType::query()->where('code', 'GROUP')->firstOrFail()->quantity_basis,
        );

        $this->assertSame(
            QuantityBasis::PASSENGERS,
            PriceType::query()->where('code', 'PASSENGER')->firstOrFail()->quantity_basis,
        );
    }

    public function test_room_and_fixed_prices_use_units_as_quantity_basis(): void
    {
        $this->assertSame(
            QuantityBasis::UNITS,
            PriceType::query()->where('code', 'ROOM')->firstOrFail()->quantity_basis,
        );

        $this->assertSame(
            QuantityBasis::UNITS,
            PriceType::query()->where('code', 'FIXED')->firstOrFail()->quantity_basis,
        );
    }
}
