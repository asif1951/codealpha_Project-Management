<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        $currentUser = Auth::user();
        
        return Inertia::render('ManageUsers', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:user,admin'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        return redirect()->back();
    }

    public function update(Request $request, User $user)
    {
        // Don't allow changing own role if you're the only admin
        if ($user->id === Auth::id() && $request->has('role') && $request->role !== $user->role) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount === 1 && $user->isAdmin()) {
                return redirect()->back()->withErrors(['role' => 'Cannot demote the only admin user.']);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:user,admin'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->has('role')) {
            $userData['role'] = $request->role;
        }

        $user->update($userData);

        return redirect()->back();
    }

    public function destroy(User $user)
    {
        // Prevent deleting your own account
        if ($user->id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deleting the last admin
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount === 1) {
                return redirect()->back()->withErrors(['error' => 'Cannot delete the only admin user.']);
            }
        }

        $user->delete();
        return redirect()->back();
    }

    public function toggleRole(User $user)
    {
        // Don't allow changing own role if you're the only admin
        if ($user->id === Auth::id()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount === 1 && $user->isAdmin()) {
                return redirect()->back()->withErrors(['error' => 'Cannot demote yourself as the only admin.']);
            }
        }

        $user->role = $user->isAdmin() ? 'user' : 'admin';
        $user->save();

        return redirect()->back();
    }
}