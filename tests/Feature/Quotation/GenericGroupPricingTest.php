<?php

namespace Tests\Feature\Quotation;

use App\Pricing\Price\Exceptions\PriceNotFoundException;
use App\Quotation\Calculation\Actions\CalculateQuotationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenericGroupPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_uses_passengers_to_select_a_group_range_but_bills_one_group(): void
    {
        $result = $this->calculateGuide(passengerCount: 7);
        $calculated = $result['items'][0];

        $this->assertEquals(1.0, $calculated['quantity']);
        $this->assertEquals(90.0, $calculated['unit_cost']);
        $this->assertEquals(130.0, $calculated['unit_price']);
        $this->assertEquals(130.0, $calculated['subtotal_sale']);
        $this->assertSame(7, $calculated['metadata']['pricing_quantity']);
        $this->assertSame(1, $calculated['metadata']['billing_quantity']);
        $this->assertSame('GROUP', $calculated['metadata']['price_type_code']);
        $this->assertSame('PASSENGERS', $calculated['metadata']['quantity_basis']);
    }

    public function test_it_selects_the_next_guide_range_for_eleven_passengers(): void
    {
        $result = $this->calculateGuide(passengerCount: 11);
        $calculated = $result['items'][0];

        $this->assertEquals(170.0, $calculated['unit_price']);
        $this->assertEquals(170.0, $calculated['subtotal_sale']);
        $this->assertSame(11, $calculated['metadata']['pricing_quantity']);
    }

    public function test_it_ignores_prices_sent_by_the_browser_for_automatic_catalog_pricing(): void
    {
        $result = $this->calculateGuide(
            passengerCount: 7,
            untrustedAmounts: true,
        );

        $calculated = $result['items'][0];

        $this->assertEquals(90.0, $calculated['unit_cost']);
        $this->assertEquals(130.0, $calculated['unit_price']);
        $this->assertEquals(130.0, $calculated['subtotal_sale']);
        $this->assertNotSame(999999, $calculated['item']['price_id']);
    }

    public function test_it_fails_when_the_passenger_count_is_outside_configured_ranges(): void
    {
        $this->expectException(PriceNotFoundException::class);

        $this->calculateGuide(passengerCount: 31);
    }

    private function calculateGuide(
        int $passengerCount,
        bool $untrustedAmounts = false,
    ): array {
        $guide = DB::table('service_variants')
            ->where('code', 'AT-GUIAPR')
            ->first();

        $currencyId = (int) DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        $passengers = array_map(
            static fn (int $index): array => [
                'id' => $index,
                'passenger_type_id' => 1,
            ],
            range(1, $passengerCount),
        );

        return app(CalculateQuotationAction::class)->execute([
            'travel_date' => '2026-06-15',
            'currency_id' => $currencyId,
            'passengers' => $passengers,
            'itineraries' => [[
                'day_number' => 1,
                'travel_date' => '2026-06-15',
                'items' => [[
                    'service_id' => (int) $guide->service_id,
                    'service_variant_id' => (int) $guide->id,
                    'name' => 'Guía Turístico Demo',
                    'variant_name' => $guide->name,
                    'item_type' => 'CATALOG',
                    'calculation_type' => 'generic',
                    'pricing_mode' => 'AUTO_GROUP',
                    'quantity' => 99,
                    'price_id' => $untrustedAmounts ? 999999 : null,
                    'unit_cost' => $untrustedAmounts ? 1 : 0,
                    'unit_price' => $untrustedAmounts ? 1 : 0,
                ]],
            ]],
        ]);
    }
}
