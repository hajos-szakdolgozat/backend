<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $toSlug = function (string $value): string {
            $value = trim($value);
            $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
            $value = strtolower($value);
            $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?: '';
            $value = trim($value, '_');
            return $value !== '' ? $value : 'amenity';
        };

        $existingAmenities = DB::table('amenities')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get();

        $usedSlugs = [];

        foreach ($existingAmenities as $amenity) {
            $baseSlug = $toSlug((string) $amenity->name);
            $slug = $baseSlug;
            $counter = 2;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = $baseSlug . '_' . $counter;
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('amenities')
                ->where('id', $amenity->id)
                ->update(['slug' => $slug]);
        }

        $defaults = [
            [
                'slug' => 'air_conditioning',
                'name' => 'Légkondícionálás',
                'description' => 'Klímával felszerelt hajó.',
            ],
            [
                'slug' => 'jacuzzi',
                'name' => 'Jakuzzi',
                'description' => 'Jakuzzi elérhető a hajón.',
            ],
            [
                'slug' => 'extra_bed',
                'name' => 'Pótágy',
                'description' => 'Extra fekhely áll rendelkezésre.',
            ],
            [
                'slug' => 'wifi',
                'name' => 'Wifi',
                'description' => 'Vezeték nélküli internetkapcsolat.',
            ],
            [
                'slug' => 'netflix',
                'name' => 'Netflix',
                'description' => 'Netflix streaming hozzáférés.',
            ],
        ];

        foreach ($defaults as $defaultAmenity) {
            $exists = DB::table('amenities')
                ->where('slug', $defaultAmenity['slug'])
                ->exists();

            if (!$exists) {
                DB::table('amenities')->insert([
                    'slug' => $defaultAmenity['slug'],
                    'name' => $defaultAmenity['name'],
                    'description' => $defaultAmenity['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('amenities', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropUnique('amenities_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
