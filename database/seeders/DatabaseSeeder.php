<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\Shared\DocumentTypeSeeder;
use Database\Seeders\ProvidersSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\ServiceVariantSeeder;
use Database\Seeders\CurrenciesSeeder;
use Database\Seeders\Pricing\PriceListSeeder;
use Database\Seeders\Shared\PassengerTypesSeeder;
use Database\Seeders\Pricing\PriceTypeSeeder;
use Database\Seeders\Pricing\PricesSeeder;
use Database\Seeders\CRM\CustomerSeeder;
use Database\Seeders\Quotation\QuotationStatusSeeder;
use Database\Seeders\Quotation\QuotationSeeder;
use Database\Seeders\Quotation\QuotationPassengerSeeder;
use Database\Seeders\Quotation\QuotationItinerarySeeder;
use Database\Seeders\Quotation\QuotationItemSeeder;

use Database\Seeders\Demo\FrontendPricingScenarioSeeder;
use Database\Seeders\Demo\InkaRoutesTestSeeder;
use Database\Seeders\Demo\AndesTestTravelSeeder;
use Database\Seeders\Demo\CommercialPricingDemoSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            DocumentTypeSeeder::class,
            ProvidersSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            ServiceVariantSeeder::class,
            CurrenciesSeeder::class,
            PriceListSeeder::class,
            PassengerTypesSeeder::class,
            PriceTypeSeeder::class,
            PricesSeeder::class,
            CustomerSeeder::class,
            QuotationStatusSeeder::class,
            QuotationSeeder::class,
            QuotationPassengerSeeder::class,
            QuotationItinerarySeeder::class,
            QuotationItemSeeder::class,

            FrontendPricingScenarioSeeder::class,
            InkaRoutesTestSeeder::class,
            AndesTestTravelSeeder::class,
            CommercialPricingDemoSeeder::class,
            CountrySeeder::class,
        ]);
    }
}
