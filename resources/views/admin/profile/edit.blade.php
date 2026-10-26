@extends('layouts.admin')

@section('title', 'Profil Admin - Guna Mica')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Profil Admin</h4>

    <div class="card border-0 shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form action="{{ route('admin.profile.update') }}" method="post" enctype="multipart/form-data" class="row g-2">
                @csrf
                @method('PUT')

                <div class="col-12">
                    <label class="form-label">Foto Profil</label>
                    <div class="d-flex align-items-center gap-3">
                        <img
                            src="{{ !empty($admin->profile_image) ? asset('uploads/profiles/' . $admin->profile_image) : asset('images/logoB.png') }}"
                            alt="Foto Profil Admin"
                            style="width:72px;height:72px;object-fit:cover;border-radius:50%;border:1px solid #dee2e6;"
                        >
                        <input type="file" class="form-control" name="profile_image" accept="image/*">
                    </div>
                    <div class="form-text">Format: JPG/PNG/WEBP, maksimal 3MB.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" value="{{ old('username', $admin->username) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" name="alamat" rows="3">{{ old('alamat', $admin->alamat) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" class="form-control" name="password">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm px-3" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
