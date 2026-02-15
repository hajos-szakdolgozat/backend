<?php

namespace Database\Factories;

use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Boat>
 */
class BoatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'port_id' => Port::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'price_per_night' => $this->faker->numberBetween(50, 1000),
            'is_active' => $this->faker->boolean(85),
            'type' => $this->faker->randomElement([
                'Sailboat',
                'Catamaran',
                'Yacht',
                'Motorboat',
                'Fishing Boat',
                'Speedboat',
            ]),
            'year_built' => $this->faker->numberBetween(1985, (int) date('Y')),
            'width' => $this->faker->randomFloat(1, 2.0, 10.0),
            'length' => $this->faker->randomFloat(1, 5.0, 60.0),
            'draft' => $this->faker->randomFloat(1, 0.5, 5.0),
        ];
    }
}
