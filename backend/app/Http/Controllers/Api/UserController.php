<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'status' => 'in:active,inactive'
        ]);

        $validated['password'] = md5($validated['password']); // MD5 como especifica el README
        $validated['status'] = $validated['status'] ?? 'active';

        return User::create($validated);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'first_name' => 'string|max:50',
            'last_name' => 'string|max:50',
            'email' => 'email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role_id' => 'exists:roles,id',
            'status' => 'in:active,inactive'
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = md5($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return $user;
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Simple login endpoint for frontend (development/demo only).
     * Expects: { email, password }
     * Compares md5(password) with stored password and returns user on success.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($user->password !== md5($validated['password'])) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Return user without password field
        $userArray = $user->toArray();
        unset($userArray['password']);

        return response()->json($userArray);
    }
}