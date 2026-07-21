<?php

declare(strict_types = 1);

final class VehicleFactory
{
    public function definition(): array
    {
        return [
            'competing_model' => fake()
                ->optional()
                ->randomElement([
                    'Ferrari 296', 'Ferrari Roma', 'Porsche 911', 'Lamborghini Huracan',
                ]),
        ];
    }
}
