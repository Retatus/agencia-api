<?php

namespace Tests\Feature\Quotation;

use App\Quotation\Models\Quotation;
use App\Quotation\Services\QuotationWorkflowService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuotationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_draft_cannot_skip_directly_to_sent(): void
    {
        $this->expectException(DomainException::class);

        app(QuotationWorkflowService::class)->transition(
            Quotation::query()->where('code', 'COT-000001')->firstOrFail(),
            'SENT',
        );
    }

    public function test_dirty_quotation_cannot_be_marked_ready(): void
    {
        $quotation = Quotation::query()->where('code', 'COT-000001')->firstOrFail();
        $quotation->update([
            'calculation_status' => Quotation::CALCULATION_DIRTY,
            'pending_calculation_items' => ['service-test'],
        ]);

        $this->expectException(DomainException::class);

        app(QuotationWorkflowService::class)->transition($quotation, 'READY');
    }

    public function test_ready_quotation_is_locked_for_ordinary_edits(): void
    {
        $quotation = $this->validDraft();
        $workflow = app(QuotationWorkflowService::class);

        $quotation = $workflow->transition($quotation, 'READY');

        $this->assertSame('READY', $quotation->status->code);
        $this->assertFalse($workflow->allowedActions($quotation)['edit']);

        $this->expectException(DomainException::class);
        $workflow->assertEditable($quotation);
    }

    public function test_cancellation_requires_a_reason(): void
    {
        $this->expectException(DomainException::class);

        app(QuotationWorkflowService::class)->transition(
            Quotation::query()->where('code', 'COT-000001')->firstOrFail(),
            'CANCELLED',
        );
    }

    private function validDraft(): Quotation
    {
        $quotation = Quotation::query()->where('code', 'COT-000001')->firstOrFail();
        $priceId = DB::table('prices')->value('id');

        DB::table('quotation_items')
            ->whereIn('quotation_itinerary_id', $quotation->itineraries()->pluck('id'))
            ->where('item_type', 'CATALOG')
            ->update(['price_id' => $priceId]);

        $quotation->update([
            'calculation_status' => Quotation::CALCULATION_CURRENT,
            'pending_calculation_items' => [],
            'calculation_dirty_reasons' => [],
            'commercial_valid_until' => today()->addDay(),
            'total' => 100,
        ]);

        return $quotation->fresh();
    }
}
