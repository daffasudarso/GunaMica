@extends('layouts.admin')

@section('title', 'Ulasan Pelanggan - Admin Guna Mica')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Ulasan Pelanggan</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-9"><input class="form-control" name="search" value="{{ $search }}" placeholder="Cari user, produk, resi, atau ulasan..."></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Cari</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>User</th>
                    <th>Produk</th>
                    <th>Resi</th>
                    <th>Rating</th>
                    <th>Ulasan</th>
                    <th>Media</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td><span class="local-time" data-utc="{{ $review->created_at }}">{{ $review->created_at }}</span></td>
                        <td>{{ $review->username }}</td>
                        <td>{{ $review->nama_produk }}</td>
                        <td>{{ $review->resi ?: '-' }}</td>
                        <td>{{ str_repeat('★', (int) $review->rating) }}</td>
                        <td style="min-width:220px;">{{ $review->clean_review ?: '-' }}</td>
                        <td style="min-width:110px;">
                            @if ($review->media_file)
                                @if ($review->media_is_video)
                                    <video controls preload="metadata" style="width:96px;height:96px;object-fit:cover;border-radius:8px;">
                                        <source src="{{ asset('uploads/reviews/' . $review->media_file) }}">
                                    </video>
                                @else
                                    <img src="{{ asset('uploads/reviews/' . $review->media_file) }}" alt="Media ulasan" style="width:96px;height:96px;object-fit:cover;border-radius:8px;">
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus ulasan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Belum ada ulasan pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reviews->links() }}
</div>
@endsection
