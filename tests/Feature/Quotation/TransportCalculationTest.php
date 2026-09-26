<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Calculation\Actions\CalculateQuotationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransportCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_calculates_two_vans_for_seventeen_passengers_from_the_full_payload(): void
    {
        $van = DB::table('service_variants')
            ->where('code', 'VAN')
            ->first();

        $currencyId = (int) DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $passengers = array_map(
            static fn (int $index): array => [
                'id' => $index,
                'passenger_type_id' => 1,
            ],
            range(1, 17),
        );

        $result = app(CalculateQuotationAction::class)->execute([
            'travel_date' => '2026-06-15',
            'currency_id' => $currencyId,
            'passengers' => $passengers,
            'itineraries' => [
                [
                    'day_number' => 1,
                    'travel_date' => '2026-06-15',
                    'items' => [
                        [
                            'service_id' => (int) $van->service_id,
                            'name' => 'Transporte',
                            'calculation_type' => 'transport',
                            'passengers' => $passengers,
                            'vehicle_types' => [
                                [
                                    'service_variant_id' => (int) $van->id,
                                    'name' => $van->name,
                                    'min_capacity' => (int) $van->min_capacity,
                                    'max_capacity' => (int) $van->max_capacity,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $recommendation = $result['items'][0]['metadata']['recommendations'][0];

        $this->assertSame(2, $recommendation['total_vehicles']);
        $this->assertSame([9, 8], array_column($recommendation['priced_units'], 'passenger_count'));
        $this->assertEquals(290.0, $recommendation['total_cost']);
        $this->assertEquals(360.0, $recommendation['total_sale']);
        $this->assertEquals(360.0, $result['items'][0]['subtotal_sale']);
    }
}