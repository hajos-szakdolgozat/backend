<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use Illuminate\Http\Request;

class BoatController extends Controller
{
    public function index()
    {
        $boats = Boat::with(['user', 'port', 'boatImages'])->get();

        return response()->json($boats, 200);
    }

    public function show($id)
    {
        $boat = Boat::with(['user', 'port', 'boatImages'])->find($id);

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
            'price_per_night' => 'required|integer|min:0',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'type' => 'required|string|max:255',
            'year_built' => 'required|integer|min:1900',
            'capacity' => 'required|integer|min:1',
            'width' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'draft' => 'required|numeric|min:0',
        ]);

        $boat = Boat::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));
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
                'port_id' => 'sometimes|exists:ports,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price_per_night' => 'sometimes|integer|min:0',
                'currency' => 'sometimes|string|max:3',
                'is_active' => 'boolean',
                'type' => 'sometimes|string|max:255',
                'year_built' => 'sometimes|integer|min:1900',
                'capacity' => 'sometimes|integer|min:1',
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
