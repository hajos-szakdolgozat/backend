<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone_number' => ['sometimes', 'regex:/^\+?[0-9 ]{7,20}$/'],
            'avatar' => 'sometimes|image|max:5120',
            'remove_avatar' => 'sometimes|boolean',
        ]);

        if (!empty($validatedData['remove_avatar']) && $user->avatar_path) {
            $storedPath = str_starts_with($user->avatar_path, 'storage/')
                ? substr($user->avatar_path, 8)
                : $user->avatar_path;

            Storage::disk('public')->delete($storedPath);
            $validatedData['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                $oldPath = str_starts_with($user->avatar_path, 'storage/')
                    ? substr($user->avatar_path, 8)
                    : $user->avatar_path;
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar_path'] = 'storage/'.$path;
        }

        unset($validatedData['avatar'], $validatedData['remove_avatar']);

        $user->update($validatedData);

        return response()->json($user->fresh());
    }

    public function index()
    {
        $users = User::all(); // lekérdezi az összes felhasználót

        return response()->json($users);
    }
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|string|min:6',
            'avatar_path' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|max:255',
            'phone_number' => ['sometimes', 'regex:/^\+?[0-9 ]{7,20}$/'],
            'email_verified_at' => 'sometimes|date',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
