@extends('layouts.admin')

@section('title', 'Log Asisten - Admin Guna Mica')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Log Percakapan Asisten</h4>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Cari pesan atau balasan...">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered bg-white align-middle">
            <thead class="table-light">
                <tr>
                    <th width="180">Waktu</th>
                    <th width="150">User ID</th>
                    <th>Pesan User</th>
                    <th>Balasan Bot</th>
                    <th width="100">Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="small">
                            <span class="local-time" data-utc="{{ $log->created_at }}">
                                {{ $log->created_at }}
                            </span>
                        </td>
                        <td>
                            @if ($log->user_id > 0)
                                <span class="badge bg-info text-dark">User #{{ $log->user_id }}</span>
                            @else
                                <span class="badge bg-secondary text-white">Guest</span>
                            @endif
                        </td>
                        <td><div class="text-wrap" style="max-width: 300px;">{{ $log->user_message }}</div></td>
                        <td><div class="text-wrap" style="max-width: 300px;">{{ $log->bot_reply }}</div></td>
                        <td>
                            @php
                                $badgeClass = match($log->source) {
                                    'faq' => 'bg-success',
                                    'ai' => 'bg-primary',
                                    'fallback' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($log->source) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada log percakapan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection

