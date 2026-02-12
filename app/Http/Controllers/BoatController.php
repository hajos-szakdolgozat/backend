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

    public function store(){
        
    }

}
