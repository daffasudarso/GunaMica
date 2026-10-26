@extends('layouts.admin')

@section('title', 'Admin FAQ Asisten - Guna Mica')

@section('content')
<div class="container">
    <h4 class="mb-3">Kelola FAQ Asisten Admin</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-9"><input class="form-control" name="search" value="{{ $search }}" placeholder="Cari pertanyaan..."></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Cari</button></div>
    </form>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6>Tambah FAQ</h6>
            <form action="{{ route('admin.faq.store') }}" method="post" class="row g-2">
                @csrf
                <div class="col-md-5"><input class="form-control" name="question" placeholder="Pertanyaan" required></div>
                <div class="col-md-5"><textarea class="form-control" name="answer" rows="1" placeholder="Jawaban" required></textarea></div>
                <div class="col-md-2 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Aktif</label></div></div>
                <div class="col-md-12 d-grid"><button class="btn btn-success">Tambah FAQ</button></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Status</th>
                    <th width="250">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faqs as $faq)
                    <tr>
                        <td>{{ $faq->id }}</td>
                        <td>{{ $faq->question }}</td>
                        <td>{{ $faq->answer }}</td>
                        <td>{{ $faq->is_active ? 'aktif' : 'nonaktif' }}</td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                <form action="{{ route('admin.faq.update', $faq->id) }}" method="post" class="mt-2 d-grid gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="question" value="{{ $faq->question }}" required>
                                    <textarea class="form-control form-control-sm" name="answer" rows="2" required>{{ $faq->answer }}</textarea>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked((int) $faq->is_active === 1)><label class="form-check-label">Aktif</label></div>
                                    <button class="btn btn-sm btn-primary">Simpan</button>
                                </form>
                            </details>
                            <form class="mt-2" action="{{ route('admin.faq.destroy', $faq->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus FAQ ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada FAQ.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $faqs->links() }}
</div>
@endsection
