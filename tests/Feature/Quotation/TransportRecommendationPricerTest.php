<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Calculation\Services\TransportRecommendationPricer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransportRecommendationPricerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_prices_two_vans_for_seventeen_passengers_using_the_eight_to_ten_range(): void
    {
        $vanId = (int) DB::table('service_variants')
            ->where('code', 'VAN')
            ->value('id');

        $currencyId = (int) DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $recommendation = [
            'vehicles' => [
                [
                    'service_variant_id' => $vanId,
                    'name' => 'Van',
                    'quantity' => 2,
                    'minimum_capacity_per_vehicle' => 4,
                    'capacity_per_vehicle' => 10,
                    'total_capacity' => 20,
                ],
            ],
            'total_capacity' => 20,
            'unused_capacity' => 3,
            'total_vehicles' => 2,
            'total_cost' => 0,
            'total_sale' => 0,
        ];

        $priced = app(TransportRecommendationPricer::class)->price(
            recommendation: $recommendation,
            passengerCount: 17,
            currencyId: $currencyId,
            serviceDate: '2026-06-15',
        );

        $this->assertNotNull($priced);
        $this->assertSame([9, 8], array_column($priced['priced_units'], 'passenger_count'));
        $this->assertSame([9, 8], $priced['vehicles'][0]['pricing_quantities']);
        $this->assertEquals(290.0, $priced['total_cost']);
        $this->assertEquals(360.0, $priced['total_sale']);

        foreach ($priced['priced_units'] as $unit) {
            $this->assertEquals(145.0, $unit['unit_cost']);
            $this->assertEquals(180.0, $unit['unit_price']);
            $this->assertNotNull($unit['price_id']);
        }
    }
}