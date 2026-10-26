<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('role')
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
            'alamat' => ['nullable', 'string'],
        ]);

        User::query()->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'alamat' => $data['alamat'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
            'alamat' => ['nullable', 'string'],
        ]);

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->alamat = $data['alamat'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data user diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ((int) Auth::id() === $id) {
            return redirect()->route('admin.users.index')->with('error', 'Akun aktif tidak dapat dihapus.');
        }

        User::query()->where('id', $id)->delete();

        return redirect()->route('admin.users.index')->with('success', 'User dihapus.');
    }
}
