<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Calculation\Actions\CalculateQuotationAction;
use Database\Seeders\Demo\AndesTestTravelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccommodationCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prices_the_recommended_rooms_in_the_backend(): void
    {
        $this->seed();
        $this->seed(AndesTestTravelSeeder::class);

        $service = DB::table('services')->where('code', 'AT-HOTEL')->first();
        $variants = DB::table('service_variants')
            ->where('service_id', $service->id)
            ->orderBy('id')
            ->get();
        $currencyId = (int) DB::table('currencies')->where('code', 'USD')->value('id');
        $passengers = array_map(
            static fn (int $id): array => ['id' => $id, 'passenger_type_id' => 1],
            range(1, 6),
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
                    'calculation_type' => 'accommodation',
                    'duration' => 2,
                    'passengers' => $passengers,
                    'room_types' => $variants->map(fn ($variant): array => [
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

        $this->assertSame(2, $selected['total_rooms']);
        $this->assertSame('AT-TPL', $this->variantCode($selected['rooms'][0]['service_variant_id']));
        $this->assertSame(2, $selected['rooms'][0]['quantity']);
        $this->assertSame(2, $selected['rooms'][0]['nights']);
        $this->assertNotNull($selected['rooms'][0]['price_id']);
        $this->assertEquals(480.0, $selected['total_cost']);
        $this->assertEquals(640.0, $selected['total_sale']);
        $this->assertEquals(640.0, $result['items'][0]['subtotal_sale']);
    }

    private function variantCode(int $variantId): string
    {
        return (string) DB::table('service_variants')
            ->where('id', $variantId)
            ->value('code');
    }
}
