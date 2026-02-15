<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoatImage>
 */
class BoatImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'boats/' . $this->faker->uuid() . '.jpg',
            'is_thumbnail' => $this->faker->boolean(25),
        ];
    }
}
