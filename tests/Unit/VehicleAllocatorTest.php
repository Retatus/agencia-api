<?php

namespace Tests\Unit\Quotation\Calculation;

use App\Quotation\Calculation\Services\VehicleAllocator;
use PHPUnit\Framework\TestCase;

class VehicleAllocatorTest extends TestCase
{
    public function test_it_recommends_one_van_instead_of_three_cars_for_seven_passengers(): void
    {
        $recommendations = $this->allocator()->recommend(
            passengers: $this->passengers(7),
            vehicleTypes: $this->vehicleTypes(),
        );

        $selected = $recommendations[0];

        $this->assertTrue($selected['recommended']);
        $this->assertSame(1, $selected['rank']);
        $this->assertSame(1, $selected['total_vehicles']);
        $this->assertSame(10, $selected['total_capacity']);
        $this->assertSame(3, $selected['unused_capacity']);
        $this->assertSame(5, $selected['vehicles'][0]['service_variant_id']);
        $this->assertSame(1, $selected['vehicles'][0]['quantity']);
        $this->assertEquals(90.0, $selected['total_cost']);
        $this->assertEquals(120.0, $selected['total_sale']);
    }

    public function test_it_recommends_a_car_for_three_passengers(): void
    {
        $selected = $this->allocator()->recommend(
            passengers: $this->passengers(3),
            vehicleTypes: $this->vehicleTypes(),
        )[0];

        $this->assertSame(4, $selected['vehicles'][0]['service_variant_id']);
        $this->assertSame(1, $selected['total_vehicles']);
        $this->assertSame(0, $selected['unused_capacity']);
        $this->assertEquals(70.0, $selected['total_cost']);
    }

    public function test_it_returns_no_recommendations_without_passengers(): void
    {
        $this->assertSame([], $this->allocator()->recommend(
            passengers: [],
            vehicleTypes: $this->vehicleTypes(),
        ));
    }

    public function test_it_ignores_vehicles_without_capacity(): void
    {
        $recommendations = $this->allocator()->recommend(
            passengers: $this->passengers(3),
            vehicleTypes: [
                [
                    'service_variant_id' => 99,
                    'name' => 'Inválido',
                    'max_capacity' => 0,
                    'unit_cost' => 1,
                    'unit_price' => 1,
                ],
                $this->vehicleTypes()[0],
            ],
        );

        $this->assertSame(4, $recommendations[0]['vehicles'][0]['service_variant_id']);
    }

    public function test_it_respects_the_recommendation_limit_and_ranking(): void
    {
        $recommendations = $this->allocator()->recommend(
            passengers: $this->passengers(7),
            vehicleTypes: $this->vehicleTypes(),
            limit: 2,
        );

        $this->assertCount(2, $recommendations);
        $this->assertSame([1, 2], array_column($recommendations, 'rank'));
        $this->assertSame([true, false], array_column($recommendations, 'recommended'));
    }

    private function allocator(): VehicleAllocator
    {
        return new VehicleAllocator();
    }

    private function passengers(int $quantity): array
    {
        return array_map(
            static fn (int $index): array => ['id' => $index],
            range(1, $quantity),
        );
    }

    private function vehicleTypes(): array
    {
        return [
            [
                'service_variant_id' => 4,
                'name' => 'Auto',
                'min_capacity' => 1,
                'max_capacity' => 3,
                'unit_cost' => 70,
                'unit_price' => 90,
            ],
            [
                'service_variant_id' => 5,
                'name' => 'Van',
                'min_capacity' => 4,
                'max_capacity' => 10,
                'unit_cost' => 90,
                'unit_price' => 120,
            ],
        ];
    }
}
