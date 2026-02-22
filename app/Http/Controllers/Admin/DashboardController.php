<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicLog;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers    = User::where('is_admin', false)->count();
        $totalLicenses = License::count();
        $activeLicenses = License::where('user_enable', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })->count();
        $expiredLicenses = License::where('user_enable', true)
            ->where('expires_at', '<=', now())
            ->whereNotNull('expires_at')
            ->count();
        $recentLogs = LicLog::with(['user', 'license'])->latest()->limit(10)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalLicenses',
            'activeLicenses',
            'expiredLicenses',
            'recentLogs'
        ));
    }
}
