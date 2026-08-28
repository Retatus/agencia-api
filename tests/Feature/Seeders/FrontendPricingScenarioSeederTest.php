<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\Demo\FrontendPricingScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontendPricingScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_idempotent_frontend_pricing_scenario(): void
    {
        $this->seed();
        $this->seed(FrontendPricingScenarioSeeder::class);
        $this->seed(FrontendPricingScenarioSeeder::class);

        $providerId = (int) DB::table('providers')
            ->where('code', 'DEMO-PRICING')
            ->value('id');

        $serviceIds = DB::table('services')
            ->where('provider_id', $providerId)
            ->whereIn('code', ['DEM-HOTEL', 'DEM-TRANS', 'DEM-TICK'])
            ->pluck('id');

        $variantIds = DB::table('service_variants')
            ->whereIn('service_id', $serviceIds)
            ->pluck('id');

        $this->assertNotSame(0, $providerId);
        $this->assertCount(3, $serviceIds);
        $this->assertCount(8, $variantIds);
        $this->assertSame(
            11,
            DB::table('prices')->whereIn('service_variant_id', $variantIds)->count(),
        );
    }
}
