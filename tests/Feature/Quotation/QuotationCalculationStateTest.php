<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCalculationStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_marks_catalog_items_as_pending_when_pricing_inputs_change(): void
    {
        $quotation = Quotation::query()
            ->whereHas('itineraries.items', fn ($query) => $query
                ->where('item_type', 'CATALOG'))
            ->firstOrFail();

        $quotation->markCalculationDirty('PASSENGER_COUNT_CHANGED');
        $quotation->refresh();

        $this->assertSame(Quotation::CALCULATION_DIRTY, $quotation->calculation_status);
        $this->assertContains(
            'PASSENGER_COUNT_CHANGED',
            $quotation->calculation_dirty_reasons,
        );
        $this->assertNotEmpty($quotation->pending_calculation_items);
        $this->assertNull($quotation->calculated_at);
    }

    public function test_it_does_not_dirty_a_quotation_without_catalog_items(): void
    {
        $quotation = Quotation::query()->firstOrFail();

        $quotation->itineraries()
            ->with('items')
            ->get()
            ->each(fn ($itinerary) => $itinerary->items()->delete());

        $quotation->markCalculationDirty('PASSENGER_COUNT_CHANGED');
        $quotation->refresh();

        $this->assertSame(Quotation::CALCULATION_CURRENT, $quotation->calculation_status);
    }
}
