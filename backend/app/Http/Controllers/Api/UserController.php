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
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
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
            'name' => 'string|max:50',
            'email' => 'email|unique:users,email,' . $id,
            'password' => 'string|min:6',
            'role_id' => 'exists:roles,id',
            'status' => 'in:active,inactive'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = md5($validated['password']);
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
}