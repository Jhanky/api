<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'address' => $request->address,
            'is_active' => $request->get('is_active', true),
        ]);

        // Assign roles
        if ($request->has('roles')) {
            foreach ($request->roles as $role) {
                $user->assignRole($role);
            }
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'birth_date', 
            'gender', 'address', 'is_active'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update roles
        if ($request->has('roles')) {
            $user->roles()->detach();
            foreach ($request->roles as $role) {
                $user->assignRole($role);
            }
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . basename($user->avatar));
        }

        $file = $request->file('avatar');
        $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
        $file->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => $user->avatar,
        ]);
    }
}
