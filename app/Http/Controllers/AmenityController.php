<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        if (!Schema::hasColumn('amenities', 'slug')) {
            return;
        }

        $hasDescriptionColumn = Schema::hasColumn('amenities', 'description');

        foreach (self::DEFAULT_AMENITIES as $slug => $label) {
            $attributes = ['slug' => $slug];
            $values = ['name' => $label];

            if ($hasDescriptionColumn) {
                $values['description'] = 'Alapértelmezett felszereltség';
            }

            Amenity::query()->firstOrCreate($attributes, $values);
        }
    }

    public function index()
    {
        $hasSlugColumn = Schema::hasColumn('amenities', 'slug');
        $hasDescriptionColumn = Schema::hasColumn('amenities', 'description');

        $this->ensureDefaultAmenities();

        $columns = ['id', 'name'];

        if ($hasSlugColumn) {
            $columns[] = 'slug';
        }

        if ($hasDescriptionColumn) {
            $columns[] = 'description';
        }

        return Amenity::query()
            ->select($columns)
            ->orderBy('name')
            ->get()
            ->map(function (Amenity $amenity) use ($hasSlugColumn, $hasDescriptionColumn) {
                return [
                    'id' => $amenity->id,
                    'slug' => $hasSlugColumn
                        ? $amenity->slug
                        : $this->normalizeSlug((string) $amenity->name),
                    'name' => $amenity->name,
                    'description' => $hasDescriptionColumn ? $amenity->description : null,
                ];
            })
            ->values();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $name = trim((string) $validated['name']);
        $slug = $this->normalizeSlug($name);
        $hasSlugColumn = Schema::hasColumn('amenities', 'slug');
        $hasDescriptionColumn = Schema::hasColumn('amenities', 'description');

        $existingAmenity = Amenity::query()
            ->when(
                $hasSlugColumn,
                fn ($query) => $query->where('slug', $slug),
                fn ($query) => $query->where('name', $name)
            )
            ->first();

        if ($existingAmenity) {
            return response()->json([
                'id' => $existingAmenity->id,
                'slug' => $hasSlugColumn
                    ? $existingAmenity->slug
                    : $this->normalizeSlug((string) $existingAmenity->name),
                'name' => $existingAmenity->name,
                'description' => $hasDescriptionColumn ? $existingAmenity->description : null,
            ], 200);
        }

        $payload = [
            'name' => $name,
        ];

        if ($hasSlugColumn) {
            $payload['slug'] = $slug;
        }

        if ($hasDescriptionColumn) {
            $payload['description'] = $validated['description'] ?? 'Kézzel hozzáadott felszereltség';
        }

        $amenity = Amenity::create($payload);

        return response()->json([
            'id' => $amenity->id,
            'slug' => $hasSlugColumn
                ? $amenity->slug
                : $this->normalizeSlug((string) $amenity->name),
            'name' => $amenity->name,
            'description' => $hasDescriptionColumn ? $amenity->description : null,
        ], 201);
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
            $hasSlugColumn = Schema::hasColumn('amenities', 'slug');

            $existsWithSameSlug = Amenity::query()
                ->when(
                    $hasSlugColumn,
                    fn ($query) => $query->where('slug', $newSlug),
                    fn ($query) => $query->where('name', $newName)
                )
                ->where('id', '!=', $amenity->id)
                ->exists();

            if ($existsWithSameSlug) {
                return response()->json([
                    'message' => 'Ez a felszereltség már létezik.',
                ], 422);
            }

            $validated['name'] = $newName;

            if ($hasSlugColumn) {
                $validated['slug'] = $newSlug;
            }
        }

        if (!Schema::hasColumn('amenities', 'description')) {
            unset($validated['description']);
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
