<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Actions\CalculateQuotationTotalsAction;
use App\Quotation\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CalculateQuotationTotalsActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_recalculates_totals_using_items_from_all_itineraries(): void
    {
        $quotation = $this->createQuotation(
            discount: 10,
            tax: 18,
        );

        $firstItineraryId = $this->createItinerary(
            quotationId: $quotation->getKey(),
            dayNumber: 1,
        );

        $secondItineraryId = $this->createItinerary(
            quotationId: $quotation->getKey(),
            dayNumber: 2,
        );

        $this->createItem($firstItineraryId, subtotal: 100);
        $this->createItem($secondItineraryId, subtotal: 50);

        $result = app(CalculateQuotationTotalsAction::class)
            ->execute($quotation);

        $this->assertSame('150.00', $result->subtotal);
        $this->assertSame('158.00', $result->total);

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->getKey(),
            'subtotal' => 150,
            'discount' => 10,
            'tax' => 18,
            'total' => 158,
        ]);
    }

    public function test_it_does_not_include_soft_deleted_items(): void
    {
        $quotation = $this->createQuotation();

        $itineraryId = $this->createItinerary(
            quotationId: $quotation->getKey(),
            dayNumber: 1,
        );

        $this->createItem($itineraryId, subtotal: 80);
        $this->createItem(
            itineraryId: $itineraryId,
            subtotal: 999,
            deletedAt: now(),
        );

        $result = app(CalculateQuotationTotalsAction::class)
            ->execute($quotation);

        $this->assertSame('80.00', $result->subtotal);
        $this->assertSame('80.00', $result->total);
    }

    private function createQuotation(
        float $discount = 0,
        float $tax = 0,
    ): Quotation {
        $id = DB::table('quotations')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'code' => 'TEST-' . Str::upper(Str::random(8)),
            'customer_id' => DB::table('customers')->value('id'),
            'currency_id' => DB::table('currencies')->value('id'),
            'quotation_status_id' => DB::table('quotation_statuses')->value('id'),
            'exchange_rate' => 1,
            'subtotal' => 999,
            'discount' => $discount,
            'tax' => $tax,
            'total' => 999,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Quotation::query()->findOrFail($id);
    }

    private function createItinerary(
        int $quotationId,
        int $dayNumber,
    ): int {
        return DB::table('quotation_itineraries')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'quotation_id' => $quotationId,
            'day_number' => $dayNumber,
            'sort_order' => $dayNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createItem(
        int $itineraryId,
        float $subtotal,
        mixed $deletedAt = null,
    ): void {
        DB::table('quotation_items')->insert([
            'uuid' => (string) Str::uuid(),
            'quotation_itinerary_id' => $itineraryId,
            'item_type' => 'CUSTOM',
            'calculation_type' => 'generic',
            'name' => 'Servicio de prueba',
            'quantity' => 1,
            'unit_cost' => $subtotal,
            'unit_price' => $subtotal,
            'subtotal' => $subtotal,
            'subtotal_cost' => $subtotal,
            'subtotal_sale' => $subtotal,
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deletedAt,
        ]);
    }
}
