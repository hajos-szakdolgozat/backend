<?php

namespace Database\Factories;

use App\Models\Amenity;
use App\Models\Boat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoatAmenity>
 */
class BoatAmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'boat_id' => Boat::factory(),
            'amenity_id' => Amenity::factory(),
        ];
    }
}
