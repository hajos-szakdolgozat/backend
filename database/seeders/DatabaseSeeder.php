<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Boat;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BalatonPortSeeder::class);

        $users = collect([
            ['name' => 'Kiss Adam', 'email' => 'adam.kiss@example.com'],
            ['name' => 'Nagy Peter', 'email' => 'peter.nagy@example.com'],
            ['name' => 'Toth Bence', 'email' => 'bence.toth@example.com'],
            ['name' => 'Szabo Levente', 'email' => 'levente.szabo@example.com'],
            ['name' => 'Varga Daniel', 'email' => 'daniel.varga@example.com'],
            ['name' => 'Kovacs Mate', 'email' => 'mate.kovacs@example.com'],
            ['name' => 'Farkas David', 'email' => 'david.farkas@example.com'],
            ['name' => 'Balogh Mark', 'email' => 'mark.balogh@example.com'],
            ['name' => 'test user', 'email' => 'test@example.com'],
        ])->map(fn(array $row) => User::firstOrCreate(
            ['email' => $row['email']],
            [
                'name' => $row['name'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        ))->values();

        $ports = collect([
            ['name' => 'Balatoni kikoto', 'city' => 'Balatonfured', 'longitude' => 17.8861, 'latitude' => 46.9581],
            ['name' => 'Siofoki vitorlaskikoto', 'city' => 'Siofok', 'longitude' => 18.0518, 'latitude' => 46.9106],
            ['name' => 'Keszthelyi kikoto', 'city' => 'Keszthely', 'longitude' => 17.2456, 'latitude' => 46.7656],
            ['name' => 'Fonyodi kikoto', 'city' => 'Fonyod', 'longitude' => 17.5605, 'latitude' => 46.7512],
            ['name' => 'Alsoorsi marina', 'city' => 'Alsoors', 'longitude' => 17.9786, 'latitude' => 46.9872],
            ['name' => 'Balatonlellei kikoto', 'city' => 'Balatonlelle', 'longitude' => 17.6979, 'latitude' => 46.7821],
        ])->map(fn(array $row) => Port::firstOrCreate(
            ['name' => $row['name']],
            [
                'city' => $row['city'],
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
            ]
        ))->values();

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

        $boatCatalog = [
            ['name' => 'Balaton Breeze 28', 'type' => 'Sailboat', 'base_price' => 240, 'capacity' => 4, 'length' => 8.6, 'width' => 2.9, 'draft' => 1.6],
            ['name' => 'Hullam 34', 'type' => 'Sailboat', 'base_price' => 320, 'capacity' => 6, 'length' => 10.2, 'width' => 3.2, 'draft' => 1.8],
            ['name' => 'Kekto Cat 38', 'type' => 'Catamaran', 'base_price' => 540, 'capacity' => 8, 'length' => 11.6, 'width' => 6.1, 'draft' => 1.2],
            ['name' => 'Panorama 22', 'type' => 'Motorboat', 'base_price' => 210, 'capacity' => 5, 'length' => 6.8, 'width' => 2.5, 'draft' => 0.9],
            ['name' => 'Silver Wake 26', 'type' => 'Speedboat', 'base_price' => 280, 'capacity' => 4, 'length' => 7.9, 'width' => 2.6, 'draft' => 0.8],
            ['name' => 'Captain 42', 'type' => 'Yacht', 'base_price' => 890, 'capacity' => 10, 'length' => 13.0, 'width' => 4.1, 'draft' => 1.9],
            ['name' => 'Fisher Lake 24', 'type' => 'Fishing Boat', 'base_price' => 180, 'capacity' => 4, 'length' => 7.1, 'width' => 2.4, 'draft' => 0.8],
        ];

        $owners = $users->shuffle()->take(6)->values();

        $boats = collect();
        foreach ($owners as $owner) {
            $listingCount = $faker->numberBetween(1, 3);

            for ($i = 0; $i < $listingCount; $i++) {
                $template = $boatCatalog[array_rand($boatCatalog)];

                $boats->push(Boat::create([
                    'user_id' => $owner->id,
                    'port_id' => $ports->random()->id,
                    'name' => $template['name'].' '.strtoupper(Str::random(2)),
                    'description' => sprintf(
                        'Well-kept %s on Lake Balaton. Ideal for day trips and weekend cruising. Comfortable cockpit, tidy cabin and reliable handling in local weather.',
                        strtolower($template['type'])
                    ),
                    'price_per_night' => $template['base_price'] + $faker->numberBetween(-25, 80),
                    'currency' => 'EUR',
                    'is_active' => $faker->boolean(90),
                    'type' => $template['type'],
                    'year_built' => $faker->numberBetween(2002, (int) date('Y')),
                    'capacity' => $template['capacity'],
                    'width' => $template['width'],
                    'length' => $template['length'],
                    'draft' => $template['draft'],
                ]));
            }
        }

        foreach ($boats as $boat) {
            $imagesCount = $faker->numberBetween(2, 4);
            $thumbnailIndex = $faker->numberBetween(1, $imagesCount);

            $imageRows = [];
            for ($i = 1; $i <= $imagesCount; $i++) {
                $path = 'boats/boat-'.$boat->id.'-'.$i.'.svg';

                Storage::disk('public')->put($path, $this->buildBoatPlaceholderSvg($boat->name, $boat->type, $i));

                $imageRows[] = [
                    'boat_id' => $boat->id,
                    'path' => $path,
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

        private function buildBoatPlaceholderSvg(string $boatName, string $boatType, int $index): string
        {
                $safeBoatName = e($boatName);
                $safeBoatType = e($boatType);

                return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800" role="img" aria-label="{$safeBoatName}">
    <defs>
        <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#bfe3ff" />
            <stop offset="100%" stop-color="#eaf6ff" />
        </linearGradient>
        <linearGradient id="water" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#2a80b9" />
            <stop offset="100%" stop-color="#12547d" />
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#sky)" />
    <rect y="450" width="1200" height="350" fill="url(#water)" />
    <path d="M190 470 C420 420, 780 420, 1010 470" fill="none" stroke="#ffffff" stroke-opacity="0.25" stroke-width="6" />
    <path d="M300 520 L950 520 L860 610 L390 610 Z" fill="#1f2f3d" />
    <rect x="520" y="400" width="160" height="120" rx="12" fill="#f2f4f7" />
    <rect x="548" y="432" width="46" height="34" fill="#99c8e8" />
    <rect x="606" y="432" width="46" height="34" fill="#99c8e8" />
    <text x="60" y="90" fill="#0f172a" font-size="52" font-family="Arial, sans-serif" font-weight="700">{$safeBoatName}</text>
    <text x="60" y="145" fill="#1f2937" font-size="34" font-family="Arial, sans-serif">{$safeBoatType} - Seed image {$index}</text>
    <text x="60" y="760" fill="#dbeafe" font-size="28" font-family="Arial, sans-serif">Lake Balaton listing preview</text>
</svg>
SVG;
        }
}
