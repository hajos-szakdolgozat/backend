<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Boat;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $testUser = User::firstWhere('email', 'test@example.com')
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $users = collect([$testUser])->merge(User::factory()->count(9)->create())->values();

        $ports = Port::factory()->count(6)->create();

        $amenityData = [
            ['name' => 'Wi-Fi', 'description' => 'Onboard internet access.'],
            ['name' => 'Air Conditioning', 'description' => 'Climate control in cabins.'],
            ['name' => 'Heating', 'description' => 'Warm cabins for colder seasons.'],
            ['name' => 'GPS', 'description' => 'Navigation system with maps.'],
            ['name' => 'Radar', 'description' => 'Radar for safer navigation.'],
            ['name' => 'Stereo System', 'description' => 'Music and media playback.'],
            ['name' => 'Kitchen', 'description' => 'Fully equipped galley.'],
            ['name' => 'Shower', 'description' => 'Freshwater shower onboard.'],
            ['name' => 'Life Jackets', 'description' => 'Safety equipment for all guests.'],
            ['name' => 'Snorkeling Gear', 'description' => 'Masks and fins included.'],
            ['name' => 'Fishing Gear', 'description' => 'Rods and tackle onboard.'],
            ['name' => 'Sun Deck', 'description' => 'Comfortable sunbathing area.'],
        ];

        $amenities = collect($amenityData)
            ->map(fn(array $item) => Amenity::firstOrCreate(['name' => $item['name']], $item))
            ->values();

        $faker = fake();
        $now = now();

        $owners = $users->shuffle()->take(5)->values();

        $boats = collect();
        foreach ($owners as $owner) {
            $boats = $boats->merge(
                Boat::factory()
                    ->count($faker->numberBetween(1, 5))
                    ->for($owner)
                    ->state(fn() => ['port_id' => $ports->random()->id])
                    ->create()
            );
        }

        foreach ($boats as $boat) {
            $imagesCount = $faker->numberBetween(2, 4);
            $thumbnailIndex = $faker->numberBetween(1, $imagesCount);

            $imageRows = [];
            for ($i = 1; $i <= $imagesCount; $i++) {
                $imageRows[] = [
                    'boat_id' => $boat->id,
                    'path' => 'boats/' . $faker->uuid() . '.jpg',
                    'is_thumbnail' => $i === $thumbnailIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('boat_images')->insert($imageRows);

            $amenityIds = $amenities
                ->random($faker->numberBetween(2, 5))
                ->pluck('id')
                ->values();

            $pivotRows = $amenityIds->map(fn(int $amenityId) => [
                'boat_id' => $boat->id,
                'amenity_id' => $amenityId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            DB::table('boat_amenities')->insert($pivotRows);
        }

        $reservationRows = [];
        $statusOptions = ['pending', 'approved', 'rejected'];
        $reservationCount = 80;

        for ($i = 0; $i < $reservationCount; $i++) {
            $start = $faker->dateTimeBetween('now', '+2 months');
            $end = (clone $start)->modify('+' . $faker->numberBetween(1, 14) . ' days');

            $reservationRows[] = [
                'user_id' => $users->random()->id,
                'boat_id' => $boats->random()->id,
                'status' => $faker->randomElement($statusOptions),
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('reservations')->insert($reservationRows);
    }
}
