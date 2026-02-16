<?php

namespace App\Http\Controllers;

use App\Models\BoatAmenity;
use Illuminate\Http\Request;

class BoatAmenityController extends Controller
{
    public function index()
    {
        return BoatAmenity::with(['boat', 'amenity'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'boat_id' => 'required|exists:boats,id',
            'amenity_id' => 'required|exists:amenities,id',
        ]);

        return BoatAmenity::create($validated);
    }

    public function destroy(BoatAmenity $boatAmenity)
    {
        $boatAmenity->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
