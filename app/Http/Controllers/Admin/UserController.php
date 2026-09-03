<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'role'      => ['required', Rule::in(['superadmin', 'admin', 'customer'])],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['boolean'],
            'photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $currentUser = Auth::guard('web')->user();
        if ($validated['role'] === 'superadmin' && $currentUser->role !== 'superadmin') {
            return back()->with('error', 'Hanya Superadmin yang dapat membuat akun Superadmin.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('users', 'public');
        }

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'photo'     => $photoPath,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $currentUser = Auth::guard('web')->user();
        if ($user->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat mengedit data Superadmin.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::guard('web')->user();
        if ($user->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat mengubah data Superadmin.');
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:30'],
            'role'      => ['required', Rule::in(['superadmin', 'admin', 'customer'])],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['boolean'],
            'photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validated['role'] === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat memberikan role Superadmin.');
        }

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role'      => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }

        // Hapus foto jika diminta
        if ($request->boolean('remove_photo') && $user->photo) {
            Storage::disk('public')->delete($user->photo);
            $data['photo'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::guard('web')->user();
        if ($user->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat menghapus akun Superadmin.');
        }

        if ($user->id === $currentUser->id) {
            abort(403, 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $currentUser = Auth::guard('web')->user();
        if ($user->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat mengubah status akun Superadmin.');
        }

        if ($user->id === $currentUser->id) {
            abort(403, 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function resetPassword(User $user)
    {
        $currentUser = Auth::guard('web')->user();

        if ($user->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat me-reset password akun Superadmin.');
        }

        $user->update([
            'password' => Hash::make('password')
        ]);

        return back()->with('success', "Password untuk user {$user->name} berhasil di-reset menjadi 'password'.");
    }
}
