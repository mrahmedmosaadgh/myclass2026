<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('my_class/super_admin/Admin/Users/Index', [
            'users' => User::with('roles')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);
        }

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'User created successfully.'
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id']
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        // Remove password from validated data if it's not provided
        if (!$request->filled('password')) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Sync roles if provided
        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);
        }

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'User updated successfully.'
        ]);
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === Auth::user()->id) {
            return back()->with('flash', [
                'type' => 'error',
                'message' => 'You cannot delete your own account.'
            ]);
        }

        $user->delete();

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'User deleted successfully.'
        ]);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'User restored successfully.'
        ]);
    }

    public function resetPassword(User $user)
    {
        $user->forceFill([
            'password' => Hash::make('12345678'),
        ])->save();

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Password has been reset to 12345678'
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roleIds' => 'required|array',
            'roleIds.*' => 'exists:roles,id'
        ]);

        $roles = Role::whereIn('id', $validated['roleIds'])->get();
        $user->syncRoles($roles);

        return redirect()->back();
    }
}



