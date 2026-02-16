<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with(['user', 'boat'])->get(); // minden kedvenc, felhasználóval és hajóval
        return response()->json($favorites);
    }

    public function store($boatId)
    {
        try {
            $favorite = Favorite::create([
                'user_id' => request()->user()->id,
                'boat_id' => $boatId,
            ]);

            return response()->json([
                'message' => 'Boat added to favorites successfully.',
                'favorite' => $favorite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($boatId)
    {
        $favorite = Favorite::where('user_id', request()->user()->id)
            ->where('boat_id', $boatId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found.'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Boat removed from favorites.'
        ]);
    }
}
