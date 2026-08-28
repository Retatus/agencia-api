<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\Demo\AndesTestTravelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AndesTestTravelSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_complete_scenario_without_duplicates_or_long_codes(): void
    {
        $this->seed();
        $this->seed(AndesTestTravelSeeder::class);
        $this->seed(AndesTestTravelSeeder::class);

        $providerId = (int) DB::table('providers')->where('code', 'ANDTEST01')->value('id');
        $serviceIds = DB::table('services')->where('provider_id', $providerId)->pluck('id');
        $variants = DB::table('service_variants')->whereIn('service_id', $serviceIds)->get();

        $this->assertNotSame(0, $providerId);
        $this->assertCount(6, $serviceIds);
        $this->assertCount(10, $variants);
        $this->assertSame(20, DB::table('prices')->whereIn('service_variant_id', $variants->pluck('id'))->count());
        $this->assertTrue($variants->every(fn ($variant): bool => strlen($variant->code) <= 10));
        $this->assertSame(0, DB::table('services')->whereRaw('CHAR_LENGTH(code) > 10')->whereIn('id', $serviceIds)->count());
    }
}
