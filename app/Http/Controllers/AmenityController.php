<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index()
    {
        return Amenity::with('boatAmenities')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        return Amenity::create($validated);
    }

    public function show(Amenity $amenity)
    {
        return $amenity->load('boatAmenities');
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => 'string',
            'description' => 'string',
        ]);

        $amenity->update($validated);

        return $amenity;
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
