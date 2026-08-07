<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Quotation\Models\QuotationItem;

use App\Quotation\Calculation\Contracts\CalculatorInterface;
use App\Quotation\Calculation\Calculators\GenericCalculator;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::model('quotationItem', QuotationItem::class);
    }
}
