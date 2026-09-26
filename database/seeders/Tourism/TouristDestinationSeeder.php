<?php

namespace Database\Seeders\Tourism;

use App\Tourism\Destination\Models\TouristDestination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TouristDestinationSeeder extends Seeder
{
    public function run(): void
    {
        $currencyId = DB::table('currencies')
            ->where('code', 'USD')
            ->value('id');

        if (!$currencyId) {
            throw new RuntimeException(
                'Debe ejecutar CurrenciesSeeder antes de TouristDestinationSeeder.'
            );
        }

        foreach ($this->destinations() as $template) {
            DB::transaction(function () use ($template, $currencyId) {
                $destination = TouristDestination::withTrashed()
                    ->where('code', $template['code'])
                    ->first();

                if (!$destination) {
                    $destination = new TouristDestination([
                        'uuid' => (string) Str::uuid(),
                        'code' => $template['code'],
                    ]);
                }

                if ($destination->trashed()) {
                    $destination->restore();
                }

                $destination->fill([
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'currency_id' => $currencyId,
                    'duration_days' => count($template['days']),
                    'active' => true,
                ])->save();

                // La plantilla se reconstruye para que el seeder sea repetible
                // y no duplique días ni servicios.
                $destination->days()->delete();

                foreach ($template['days'] as $dayIndex => $dayData) {
                    $day = $destination->days()->create([
                        'day_number' => $dayIndex + 1,
                        'title' => $dayData['title'],
                        'description' => $dayData['description'],
                        'sort_order' => $dayIndex + 1,
                    ]);

                    foreach ($dayData['items'] as $itemIndex => $itemData) {
                        $day->items()->create([
                            ...$itemData,
                            'duration' => $itemData['duration'] ?? 1,
                            'quantity' => $itemData['quantity'] ?? 1,
                            'sort_order' => $itemIndex + 1,
                            'active' => true,
                        ]);
                    }
                }
            });
        }
    }

    private function destinations(): array
    {
        return [
            [
                'code' => 'CUSCO5D',
                'name' => 'Cusco clásico 5 días',
                'description' => 'Programa inicial con ciudad de Cusco, Valle Sagrado y Machu Picchu. Los importes son referenciales.',
                'days' => [
                    [
                        'title' => 'Llegada a Cusco',
                        'description' => 'Recepción, traslado al hotel y tarde libre para aclimatación.',
                        'items' => [
                            $this->item('Traslado aeropuerto - hotel', 'Recepción y traslado privado aproximado.', 18, 30),
                            $this->item('Alojamiento en Cusco', 'Noche referencial en habitación estándar.', 45, 70),
                        ],
                    ],
                    [
                        'title' => 'City Tour Cusco',
                        'description' => 'Visita panorámica por la ciudad y complejos arqueológicos cercanos.',
                        'items' => [
                            $this->item('City Tour Cusco', 'Movilidad y operación turística aproximada.', 25, 40),
                            $this->item('Guía de turismo', 'Servicio de guía para el grupo.', 45, 70),
                            $this->item('Alojamiento en Cusco', 'Noche referencial en habitación estándar.', 45, 70),
                        ],
                    ],
                    [
                        'title' => 'Valle Sagrado de los Incas',
                        'description' => 'Excursión de día completo por los principales atractivos del Valle Sagrado.',
                        'items' => [
                            $this->item('Excursión Valle Sagrado', 'Transporte turístico aproximado.', 35, 55),
                            $this->item('Almuerzo buffet', 'Almuerzo turístico referencial.', 14, 22),
                            $this->item('Alojamiento en Valle Sagrado', 'Noche referencial.', 50, 78),
                        ],
                    ],
                    [
                        'title' => 'Machu Picchu',
                        'description' => 'Visita de día completo a Machu Picchu con servicios sujetos a disponibilidad.',
                        'items' => [
                            $this->item('Tren turístico', 'Tarifa aproximada de ida y retorno.', 85, 115),
                            $this->item('Ingreso Machu Picchu', 'Entrada aproximada pendiente de circuito.', 42, 55),
                            $this->item('Guía Machu Picchu', 'Servicio guiado aproximado.', 35, 55),
                            $this->item('Alojamiento en Cusco', 'Noche referencial en habitación estándar.', 45, 70),
                        ],
                    ],
                    [
                        'title' => 'Salida de Cusco',
                        'description' => 'Mañana libre y traslado al aeropuerto según horario de vuelo.',
                        'items' => [
                            $this->item('Traslado hotel - aeropuerto', 'Traslado privado aproximado.', 18, 30),
                        ],
                    ],
                ],
            ],
            [
                'code' => 'PUNO3D',
                'name' => 'Lago Titicaca 3 días',
                'description' => 'Programa referencial en Puno con navegación por Uros y Taquile.',
                'days' => [
                    [
                        'title' => 'Llegada a Puno',
                        'description' => 'Recepción, traslado y descanso en la ciudad de Puno.',
                        'items' => [
                            $this->item('Traslado terminal - hotel', 'Traslado local aproximado.', 12, 20),
                            $this->item('Alojamiento en Puno', 'Noche referencial.', 38, 60),
                        ],
                    ],
                    [
                        'title' => 'Islas Uros y Taquile',
                        'description' => 'Navegación de día completo por el lago Titicaca.',
                        'items' => [
                            $this->item('Navegación Titicaca', 'Lancha turística y operación aproximada.', 28, 45),
                            $this->item('Guía de turismo', 'Servicio guiado compartido.', 18, 30),
                            $this->item('Almuerzo local', 'Almuerzo turístico referencial.', 10, 16),
                            $this->item('Alojamiento en Puno', 'Noche referencial.', 38, 60),
                        ],
                    ],
                    [
                        'title' => 'Salida de Puno',
                        'description' => 'Traslado al terminal o aeropuerto según continuación del viaje.',
                        'items' => [
                            $this->item('Traslado de salida', 'Traslado local aproximado.', 12, 20),
                        ],
                    ],
                ],
            ],
            [
                'code' => 'AREQ4D',
                'name' => 'Arequipa y Colca 4 días',
                'description' => 'Plantilla referencial para conocer Arequipa y el Valle del Colca.',
                'days' => [
                    [
                        'title' => 'Llegada a Arequipa',
                        'description' => 'Recepción, traslado y recorrido introductorio por el centro histórico.',
                        'items' => [
                            $this->item('Traslado aeropuerto - hotel', 'Traslado local aproximado.', 15, 25),
                            $this->item('Alojamiento en Arequipa', 'Noche referencial.', 42, 68),
                        ],
                    ],
                    [
                        'title' => 'Arequipa - Valle del Colca',
                        'description' => 'Viaje terrestre hacia el Valle del Colca con paradas panorámicas.',
                        'items' => [
                            $this->item('Transporte al Colca', 'Movilidad turística aproximada.', 30, 48),
                            $this->item('Guía de turismo', 'Servicio guiado para el recorrido.', 22, 35),
                            $this->item('Alojamiento en Colca', 'Noche referencial.', 46, 72),
                        ],
                    ],
                    [
                        'title' => 'Cruz del Cóndor',
                        'description' => 'Visita al mirador de la Cruz del Cóndor y retorno a Arequipa.',
                        'items' => [
                            $this->item('Excursión Cruz del Cóndor', 'Transporte y operación aproximada.', 28, 45),
                            $this->item('Ingreso turístico', 'Boleto turístico referencial.', 20, 28),
                            $this->item('Alojamiento en Arequipa', 'Noche referencial.', 42, 68),
                        ],
                    ],
                    [
                        'title' => 'Salida de Arequipa',
                        'description' => 'Traslado al aeropuerto según horario de vuelo.',
                        'items' => [
                            $this->item('Traslado hotel - aeropuerto', 'Traslado local aproximado.', 15, 25),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function item(
        string $name,
        string $description,
        float $estimatedCost,
        float $estimatedPrice
    ): array {
        return [
            'name' => $name,
            'description' => $description,
            'duration' => 1,
            'quantity' => 1,
            'estimated_cost' => $estimatedCost,
            'estimated_price' => $estimatedPrice,
        ];
    }
}
