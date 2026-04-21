<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use Carbon\Carbon;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
=======
use Illuminate\Support\Str;
>>>>>>> f958af758c0f34e5ebfa0504da8874ddaaa381e4

class BoatController extends Controller
{
    public function index(Request $request)
    {
        $query = Boat::with(['user', 'port', 'boatImages'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        $type = $request->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        $minPrice = $request->query('min_price');
        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('price_per_night', '>=', (int) $minPrice);
        }

        $maxPrice = $request->query('max_price');
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('price_per_night', '<=', (int) $maxPrice);
        }

        $active = $request->query('active');
        if ($active === '1' || $active === 'true') {
            $query->where('is_active', true);
        } elseif ($active === '0' || $active === 'false') {
            $query->where('is_active', false);
        }

        $capacity = $request->query('guests');
        if ($capacity && is_numeric($capacity)) {
            $query->where('capacity', '>=', (int) $capacity);
        }

        $location = $request->query('location');
        if ($location) {
            $query->whereHas('port', function ($q) use ($location) {
                $q->where('name', 'like', "%{$location}%")
                    ->orWhere('city', 'like', "%{$location}%");
            });
        }

        $amenities = $request->query('amenities', $request->query('amenities[]', []));
        if (!is_array($amenities)) {
            $amenities = [$amenities];
        }

        $amenities = array_values(array_unique(array_filter(array_map(function ($amenity) {
            $rawValue = trim((string) $amenity);
            if ($rawValue === '') {
                return null;
            }

            $slug = Str::slug(Str::ascii($rawValue), '_');
            return $slug !== '' ? $slug : null;
        }, $amenities))));

        foreach ($amenities as $amenitySlug) {
            $query->whereHas('boatAmenities.amenity', function ($amenityQuery) use ($amenitySlug) {
                $amenityQuery->where('slug', $amenitySlug);
            });
        }

        $checkIn = $request->query('checkin');
        $checkOut = $request->query('checkout');
        if ($checkIn && $checkOut) {
            $query->whereDoesntHave('reservations', function ($reservationQuery) use ($checkIn, $checkOut) {
                $reservationQuery
                    ->where('status', 'approved')
                    ->where('start_date', '<', $checkOut)
                    ->where('end_date', '>', $checkIn);
            });
        }

        $sort = $request->query('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price_per_night');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price_per_night');
        } elseif ($sort === 'rating_desc') {
            $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count');
        } elseif ($sort === 'newest') {
            $query->latest();
        } else {
            $query->latest();
        }

        $boats = $query->get();

        return response()->json($boats, 200);
    }

    public function mine(Request $request)
    {
        $boats = Boat::with(['user', 'port', 'boatImages'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($boats, 200);
    }

    public function show($id)
    {
        $boat = Boat::with(['user', 'port', 'boatImages', 'boatAmenities.amenity'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->find($id);

        if (!$boat) {
            return response()->json([
                'message' => 'Boat not found'
            ], 404);
        }

        return response()->json($boat, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // user comes from authenticated token, not from payload
            'port_id' => 'required|exists:ports,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|integer|min:1|max:1000000',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'type' => 'required|string|max:255',
            'year_built' => 'required|integer|min:1900|max:2100',
            'capacity' => 'required|integer|min:1|max:100',
            'width' => 'required|numeric|min:0.5|max:30',
            'length' => 'required|numeric|min:2|max:120',
            'draft' => 'required|numeric|min:0.1|max:15',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'image|max:5120',
            'thumbnail_index' => 'sometimes|integer|min:0',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'integer|exists:amenities,id',
        ]);

        $amenityIds = [];
        if (array_key_exists('amenities', $validated)) {
            $amenityIds = array_values(array_unique(array_map(
                'intval',
                $validated['amenities'] ?? []
            )));
            unset($validated['amenities']);
        }

        $boat = Boat::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        if (!empty($amenityIds)) {
            $boat->boatAmenities()->createMany(array_map(function ($amenityId) {
                return ['amenity_id' => $amenityId];
            }, $amenityIds));
        }

        if ($request->hasFile('images')) {
            $thumbnailIndex = (int) $request->input('thumbnail_index', 0);
            $images = $request->file('images');

            foreach ($images as $index => $imageFile) {
                $path = $imageFile->store('boats', 'public');

                $boat->boatImages()->create([
                    'path' => $path,
                    'is_thumbnail' => $index === $thumbnailIndex,
                ]);
            }
        }

        return response()->json($boat->load(['user', 'port', 'boatImages', 'boatAmenities.amenity']), 201);
    }

    public function update(Request $request, $id)
    {
        $boat = Boat::find($id);

        if (!$boat) {
            return response()->json([
                'message' => 'Boat not found'
            ], 404);
        }

        $currentUser = $request->user();
        $isOwner = (int) $boat->user_id === (int) $currentUser->id;
        $isAdmin = $currentUser->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'message' => 'You are not allowed to edit this boat'
            ], 403);
        }

        $validated = $request->validate([
            'port_id' => 'sometimes|exists:ports,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'sometimes|integer|min:1|max:1000000',
            'currency' => 'sometimes|string|max:3',
            'is_active' => 'boolean',
            'type' => 'sometimes|string|max:255',
            'year_built' => 'sometimes|integer|min:1900|max:2100',
            'capacity' => 'sometimes|integer|min:1|max:100',
            'width' => 'sometimes|numeric|min:0.5|max:30',
            'length' => 'sometimes|numeric|min:2|max:120',
            'draft' => 'sometimes|numeric|min:0.1|max:15',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'integer|exists:amenities,id',
        ]);

        $amenityIds = null;
        if (array_key_exists('amenities', $validated)) {
            $amenityIds = array_values(array_unique(array_map(
                'intval',
                $validated['amenities'] ?? []
            )));
            unset($validated['amenities']);
        }

        $boat->update($validated);

        if ($amenityIds !== null) {
            $boat->boatAmenities()->delete();
            if (!empty($amenityIds)) {
                $boat->boatAmenities()->createMany(array_map(function ($amenityId) {
                    return ['amenity_id' => $amenityId];
                }, $amenityIds));
            }
        }

        return response()->json($boat->load(['user', 'port', 'boatImages', 'boatAmenities.amenity']), 200);
    }

    public function destroy(Request $request, Boat $boat)
    {
        $boat->loadMissing(['boatImages', 'reservations']);

        $currentUser = $request->user();
        $isOwner = (int) $boat->user_id === (int) $currentUser->id;
        $isAdmin = $currentUser->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'message' => 'You are not allowed to delete this boat'
            ], 403);
        }

        if (!$isAdmin) {
            $hasFutureReservations = $boat->reservations()
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('end_date', '>=', Carbon::today()->toDateString())
                ->exists();

            if ($hasFutureReservations) {
                return response()->json([
                    'message' => 'This boat cannot be deleted because it has active or upcoming reservations.'
                ], 422);
            }
        }

        $imagePaths = $boat->boatImages
            ->pluck('path')
            ->filter()
            ->values()
            ->all();

        DB::transaction(function () use ($boat) {
            $boat->delete();
        });

        if ($imagePaths !== []) {
            Storage::disk('public')->delete($imagePaths);
        }

        return response()->json([
            'message' => 'Boat deleted successfully'
        ], 200);
    }
}
