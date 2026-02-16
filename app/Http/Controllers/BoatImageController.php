<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\BoatImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BoatImageController extends Controller
{
    public function store(Request $request, $id)
    {
        $boat = Boat::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:5120',
            'is_thumbnail' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $imageFile = $request->file('image');

        // Ellenőrizzük, hogy a kép már létezik-e (hash alapján)
        $hash = md5_file($imageFile->getRealPath());
        $existingImage = $boat->images()->where('hash', $hash)->first();
        if ($existingImage) {
            return response()->json([
                'message' => 'This image already exists for this boat.',
                'data' => $existingImage
            ], 409);
        }

        $path = $imageFile->store('boats', 'public');

        // Ha is_thumbnail, akkor az összes többi thumbnail false lesz
        if ($request->has('is_thumbnail') && $request->is_thumbnail) {
            $boat->images()->update(['is_thumbnail' => false]);
        }

        $boatImage = new BoatImage([
            'path' => $path,
            'hash' => $hash,
            'is_thumbnail' => $request->has('is_thumbnail') ? $request->is_thumbnail : false
        ]);

        $boat->images()->save($boatImage);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => $boatImage
        ], 201);
    }

    public function destroy($id, $imageId)
    {
        $boat = Boat::findOrFail($id);
        $image = $boat->images()->findOrFail($imageId);

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ], 200);
    }
}
