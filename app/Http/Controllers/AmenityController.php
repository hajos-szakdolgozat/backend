<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmenityController extends Controller
{
    private const DEFAULT_AMENITIES = [
        'air_conditioning' => 'Légkondícionálás',
        'jacuzzi' => 'Jakuzzi',
        'extra_bed' => 'Pótágy',
        'wifi' => 'Wifi',
        'netflix' => 'Netflix',
    ];

    private function normalizeSlug(string $name): string
    {
        $slug = Str::slug(Str::ascii($name), '_');
        return $slug !== '' ? $slug : 'amenity';
    }

    private function ensureDefaultAmenities(): void
    {
        foreach (self::DEFAULT_AMENITIES as $slug => $label) {
            Amenity::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $label,
                    'description' => 'Alapértelmezett felszereltség',
                ]
            );
        }
    }

    public function index()
    {
        $this->ensureDefaultAmenities();

        return Amenity::query()
            ->select(['id', 'slug', 'name', 'description'])
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $name = trim((string) $validated['name']);
        $slug = $this->normalizeSlug($name);

        $existingAmenity = Amenity::query()->where('slug', $slug)->first();
        if ($existingAmenity) {
            return response()->json($existingAmenity, 200);
        }

        $amenity = Amenity::create([
            'slug' => $slug,
            'name' => $name,
            'description' => $validated['description'] ?? 'Kézzel hozzáadott felszereltség',
        ]);

        return response()->json($amenity, 201);
    }

    public function show(Amenity $amenity)
    {
        return $amenity->load('boatAmenities');
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
        ]);

        if (array_key_exists('name', $validated)) {
            $newName = trim((string) $validated['name']);
            $newSlug = $this->normalizeSlug($newName);

            $existsWithSameSlug = Amenity::query()
                ->where('slug', $newSlug)
                ->where('id', '!=', $amenity->id)
                ->exists();

            if ($existsWithSameSlug) {
                return response()->json([
                    'message' => 'Ez a felszereltség már létezik.',
                ], 422);
            }

            $validated['name'] = $newName;
            $validated['slug'] = $newSlug;
        }

        $amenity->update($validated);

        return $amenity;
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
