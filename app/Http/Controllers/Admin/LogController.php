<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = LicLog::with(['user', 'license']);

        if ($level = $request->get('level')) {
            $query->where('level', $level);
        }

        if ($event = $request->get('event')) {
            $query->where('event', 'like', "%{$event}%");
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        return view('admin.logs.index', compact('logs', 'level', 'event'));
    }
}
