<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Quotation\Models\QuotationItem;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\Calculators\GenericCalculator;
use App\Pricing\Price\Contracts\PriceAdjustmentPolicy;
use App\Pricing\Price\Contracts\PriceResolverInterface;
use App\Pricing\Price\Policies\NoPriceAdjustmentPolicy;
use App\Pricing\Price\Services\PriceResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CalculatorInterface::class,
            GenericCalculator::class
        );

        $this->app->bind(
            PriceResolverInterface::class,
            PriceResolver::class
        );

        $this->app->bind(
            PriceAdjustmentPolicy::class,
            NoPriceAdjustmentPolicy::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::model('quotationItem', QuotationItem::class);
    }
}
