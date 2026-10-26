<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsistenLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsistenLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');
        
        $logs = AsistenLog::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('user_message', 'like', '%' . $search . '%')
                  ->orWhere('bot_reply', 'like', '%' . $search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.asisten_logs.index', [
            'logs' => $logs,
            'search' => $search,
        ]);
    }
}

