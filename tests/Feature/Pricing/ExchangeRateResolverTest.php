<?php

namespace Tests\Feature\Pricing;

use App\Pricing\ExchangeRate\Exceptions\ExchangeRateNotFoundException;
use App\Pricing\ExchangeRate\Models\ExchangeRate;
use App\Pricing\ExchangeRate\Services\CurrencyConverter;
use App\Pricing\ExchangeRate\Services\ExchangeRateResolver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExchangeRateResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_it_resolves_the_latest_direct_rate_for_the_requested_date(): void
    {
        $this->rate('USD', 'PEN', '3.70000000', '2026-09-01');
        $this->rate('USD', 'PEN', '3.80000000', '2026-09-20');
        $this->rate('USD', 'PEN', '3.90000000', '2026-10-01');

        $resolved = app(ExchangeRateResolver::class)->resolve(
            $this->currencyId('USD'),
            $this->currencyId('PEN'),
            CarbonImmutable::parse('2026-09-27'),
        );

        $this->assertSame('3.80000000', $resolved->rate);
        $this->assertSame('2026-09-20', $resolved->effectiveDate->toDateString());
        $this->assertFalse($resolved->inverted);
    }

    public function test_it_can_use_the_inverse_rate(): void
    {
        $this->rate('USD', 'PEN', '4.00000000', '2026-09-20');

        $resolved = app(ExchangeRateResolver::class)->resolve(
            $this->currencyId('PEN'),
            $this->currencyId('USD'),
            CarbonImmutable::parse('2026-09-27'),
        );

        $this->assertSame('0.25000000', $resolved->rate);
        $this->assertTrue($resolved->inverted);
    }

    public function test_it_converts_and_rounds_money_to_two_decimals(): void
    {
        $this->rate('USD', 'PEN', '3.75000000', '2026-09-20');

        $converted = app(CurrencyConverter::class)->convert(
            '10.55',
            $this->currencyId('USD'),
            $this->currencyId('PEN'),
            CarbonImmutable::parse('2026-09-27'),
        );

        $this->assertSame('39.56', $converted['amount']);
    }

    public function test_it_fails_when_no_rate_exists(): void
    {
        $this->expectException(ExchangeRateNotFoundException::class);

        app(ExchangeRateResolver::class)->resolve(
            $this->currencyId('EUR'),
            $this->currencyId('PEN'),
            CarbonImmutable::parse('2026-09-27'),
        );
    }

    private function rate(string $from, string $to, string $rate, string $date): void
    {
        ExchangeRate::create([
            'from_currency_id' => $this->currencyId($from),
            'to_currency_id' => $this->currencyId($to),
            'rate' => $rate,
            'effective_date' => $date,
            'source' => 'TEST',
            'active' => true,
        ]);
    }

    private function currencyId(string $code): int
    {
        return (int) DB::table('currencies')->where('code', $code)->value('id');
    }
}
