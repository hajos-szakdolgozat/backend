<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use Illuminate\Http\Request;

class BoatController extends Controller
{
    public function index(Request $request)
    {
        $query = Boat::with(['user', 'port', 'boatImages'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

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

        $boats = $query->get();

        return response()->json($boats, 200);
    }

    public function show($id)
    {
        $boat = Boat::with(['user', 'port', 'boatImages'])
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
        ]);

        $boat = Boat::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

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

        return response()->json($boat->load(['user', 'port', 'boatImages']), 201);
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
        ]);

        $boat->update($validated);

        return response()->json($boat->load(['user', 'port', 'boatImages']), 200);
    }

    public function destroy(Request $request, $id)
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
                'message' => 'You are not allowed to delete this boat'
            ], 403);
        }

        $boat->delete();

        return response()->json([
            'message' => 'Boat deleted successfully'
        ], 200);
    }
}
