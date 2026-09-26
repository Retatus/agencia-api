<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\Demo\InkaRoutesTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InkaRoutesTestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_second_complete_and_idempotent_scenario(): void
    {
        $this->seed();
        $this->seed(InkaRoutesTestSeeder::class);
        $this->seed(InkaRoutesTestSeeder::class);

        $providerId = (int) DB::table('providers')->where('code', 'INKATEST1')->value('id');
        $serviceIds = DB::table('services')->where('provider_id', $providerId)->pluck('id');
        $variants = DB::table('service_variants')->whereIn('service_id', $serviceIds)->get();

        $this->assertNotSame(0, $providerId);
        $this->assertCount(6, $serviceIds);
        $this->assertCount(15, $variants);
        $this->assertSame(31, DB::table('prices')->whereIn('service_variant_id', $variants->pluck('id'))->count());
        $this->assertTrue($variants->every(fn ($variant): bool => strlen($variant->code) <= 10));
        $this->assertSame(0, DB::table('services')->whereIn('id', $serviceIds)->whereRaw('CHAR_LENGTH(code) > 10')->count());
    }
}
