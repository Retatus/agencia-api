<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Calculation\Actions\CalculateQuotationAction;
use Database\Seeders\Demo\FrontendPricingScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoTransportCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_transport_recommends_two_vans_for_seventeen_passengers(): void
    {
        $this->seed();
        $this->seed(FrontendPricingScenarioSeeder::class);

        $service = DB::table('services')->where('code', 'DEM-TRANS')->first();
        $variants = DB::table('service_variants')
            ->where('service_id', $service->id)
            ->orderBy('id')
            ->get();
        $currencyId = (int) DB::table('currencies')->where('code', 'USD')->value('id');

        $passengers = array_map(
            static fn (int $id): array => ['id' => $id, 'passenger_type_id' => 1],
            range(1, 17),
        );

        $result = app(CalculateQuotationAction::class)->execute([
            'travel_date' => '2026-08-26',
            'currency_id' => $currencyId,
            'passengers' => $passengers,
            'itineraries' => [[
                'day_number' => 1,
                'travel_date' => '2026-08-26',
                'items' => [[
                    'service_id' => (int) $service->id,
                    'name' => $service->name,
                    'calculation_type' => 'transport',
                    'passengers' => $passengers,
                    'vehicle_types' => $variants->map(fn ($variant): array => [
                        'service_variant_id' => (int) $variant->id,
                        'name' => $variant->name,
                        'min_capacity' => (int) $variant->min_capacity,
                        'max_capacity' => (int) $variant->max_capacity,
                    ])->all(),
                ]],
            ]],
        ]);

        $recommendations = $result['items'][0]['metadata']['recommendations'];

        $this->assertNotEmpty(
            $recommendations,
            $result['items'][0]['metadata']['pricing_error'] ?? 'Sin recomendaciones.',
        );

        $selected = $recommendations[0];

        $this->assertSame(2, $selected['total_vehicles']);
        $this->assertSame('D-VAN', $this->variantCode($selected['vehicles'][0]['service_variant_id']));
        $this->assertSame([9, 8], array_column($selected['priced_units'], 'passenger_count'));
        $this->assertEquals(290.0, $selected['total_cost']);
        $this->assertEquals(360.0, $selected['total_sale']);
    }

    private function variantCode(int $variantId): string
    {
        return (string) DB::table('service_variants')
            ->where('id', $variantId)
            ->value('code');
    }
}