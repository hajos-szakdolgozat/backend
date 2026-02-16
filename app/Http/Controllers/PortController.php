<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        return Port::with('boats')->get();
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'city' => 'required|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);

        return Port::create($validated);
    }
    public function show(Port $port)
    {
        return $port->load('boats');
    }
    public function update(Request $request, Port $port)
    {
        $validated = $request->validate([
            'name' => 'string',
            'city' => 'string',
            'longitude' => 'numeric',
            'latitude' => 'numeric',
        ]);

        $port->update($validated);

        return $port;
    }
    public function destroy(Port $port)
    {
        $port->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
