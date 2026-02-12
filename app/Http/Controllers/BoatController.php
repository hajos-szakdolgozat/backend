<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use Illuminate\Http\Request;

class BoatController extends Controller
{
    public function index()
    {
        $boats = Boat::with(['user', 'port'])->get();

        return response()->json($boats, 200);
    }

    public function show($id)
    {
        $boat = Boat::with(['user', 'port'])->find($id);

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
            'user_id' => 'required|exists:users,id',
            'port_id' => 'required|exists:ports,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'type' => 'required|string|max:255',
            'year_built' => 'required|integer|min:1900',
            'width' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'draft' => 'required|numeric|min:0',
        ]);

        $boat = Boat::create($validated);
        return response()->json($boat, 201);
    }

    public function update(Request $request, $id)
    { {
            $boat = Boat::find($id);

            if (!$boat) {
                return response()->json([
                    'message' => 'Boat not found'
                ], 404);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|exists:users,id',
                'port_id' => 'sometimes|exists:ports,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price_per_night' => 'sometimes|integer|min:0',
                'is_active' => 'boolean',
                'type' => 'sometimes|string|max:255',
                'year_built' => 'sometimes|integer|min:1900',
                'width' => 'sometimes|numeric|min:0',
                'length' => 'sometimes|numeric|min:0',
                'draft' => 'sometimes|numeric|min:0',
            ]);

            $boat->update($validated);

            return response()->json($boat, 200);
        }
    }

    public function destroy($id)
    {
        $boat = Boat::find($id);

        if (!$boat) {
            return response()->json([
                'message' => 'Boat not found'
            ], 404);
        }

        $boat->delete();

        return response()->json([
            'message' => 'Boat deleted successfully'
        ], 200);
    }
}
