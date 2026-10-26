<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'admin' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $admin */
        $admin = Auth::user();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,' . $admin->id],
            'alamat' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $admin->username = $data['username'];
        $admin->email = $data['email'];
        $admin->alamat = $data['alamat'] ?? null;

        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = 'admin_' . $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profiles'), $filename);

            if (!empty($admin->profile_image)) {
                $oldPath = public_path('uploads/profiles/' . $admin->profile_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $admin->profile_image = $filename;
        }

        $admin->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Profil admin berhasil diperbarui.');
    }
}
