<?php

namespace Tests\Feature\Tourism;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristDestinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_returns_a_complete_destination_template(): void
    {
        $this->seed();

        $currency = Currency::query()->firstOrFail();

        $response = $this->postJson('/api/v1/tourist-destinations', [
            'code' => 'CUSCO5D',
            'name' => 'Cusco clásico 5 días',
            'description' => 'Plantilla inicial para cotización.',
            'currency_id' => $currency->id,
            'duration_days' => 1,
            'active' => true,
            'days' => [[
                'day_number' => 1,
                'title' => 'Llegada a Cusco',
                'description' => 'Recepción y traslado aproximado.',
                'sort_order' => 1,
                'items' => [[
                    'name' => 'Traslado aeropuerto - hotel',
                    'description' => 'Servicio provisional pendiente de seleccionar proveedor.',
                    'duration' => 1,
                    'quantity' => 1,
                    'estimated_cost' => 20,
                    'estimated_price' => 30,
                    'sort_order' => 1,
                    'active' => true,
                ]],
            ]],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'CUSCO5D')
            ->assertJsonPath('data.days.0.items.0.estimated_price', '30.00');

        $uuid = $response->json('data.uuid');

        $this->getJson("/api/v1/tourist-destinations/{$uuid}")
            ->assertOk()
            ->assertJsonCount(1, 'data.days')
            ->assertJsonCount(1, 'data.days.0.items');
    }

    public function test_destination_code_is_limited_to_ten_characters(): void
    {
        $this->seed();

        $this->postJson('/api/v1/tourist-destinations', [
            'code' => 'DESTINATION1',
        ])->assertUnprocessable()->assertJsonValidationErrors('code');
    }
}
