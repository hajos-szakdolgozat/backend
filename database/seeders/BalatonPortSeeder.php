<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class BalatonPortSeeder extends Seeder
{
    /**
     * Seed a default Balaton port.
     */
    public function run(): void
    {
        Port::firstOrCreate(
            ['name' => 'Balatoni kikoto'],
            [
                'city' => 'Balatonfured',
                'longitude' => 17.8861,
                'latitude' => 46.9581,
            ]
        );
    }
}
