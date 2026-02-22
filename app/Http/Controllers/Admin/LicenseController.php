<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $query = License::with('user');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $licenses = $query->latest()->paginate(20)->withQueryString();

        return view('admin.licenses.index', compact('licenses', 'search'));
    }

    public function create(Request $request)
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();
        $selectedUserId = $request->get('user_id');

        return view('admin.licenses.create', compact('users', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'serial_number'     => 'nullable|string|max:64|unique:licenses,serial_number',
            'starts_at'         => 'required|date',
            'expires_at'        => 'nullable|date|after_or_equal:starts_at',
            'license_type'      => 'required|in:demo,monthly,yearly,lifetime',
            'product_package'   => 'required|string|max:64',
            'max_connection_count' => 'required|integer|min:1',
            'user_enable'       => 'boolean',
            'emergency'         => 'boolean',
        ]);

        $validated['serial_number'] = $validated['serial_number'] ?? strtoupper(Str::random(8) . '-' . Str::random(8) . '-' . Str::random(8));
        $validated['user_enable']   = $request->boolean('user_enable', true);
        $validated['emergency']     = $request->boolean('emergency', false);

        License::create($validated);

        return redirect()->route('admin.licenses.index')->with('success', 'Lisans oluşturuldu.');
    }

    public function edit(License $license)
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.licenses.edit', compact('license', 'users'));
    }

    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'serial_number'       => 'required|string|max:64|unique:licenses,serial_number,' . $license->id,
            'device_id'           => 'nullable|string|max:128',
            'starts_at'           => 'required|date',
            'expires_at'          => 'nullable|date',
            'license_type'        => 'required|in:demo,monthly,yearly,lifetime',
            'product_package'     => 'required|string|max:64',
            'max_connection_count' => 'required|integer|min:1',
            'user_enable'         => 'boolean',
            'emergency'           => 'boolean',
        ]);

        $validated['user_enable'] = $request->boolean('user_enable');
        $validated['emergency']   = $request->boolean('emergency');

        $license->update($validated);

        return redirect()->route('admin.licenses.index')->with('success', 'Lisans güncellendi.');
    }

    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('admin.licenses.index')->with('success', 'Lisans silindi.');
    }

    public function extendExpiry(Request $request, License $license)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $base = $license->expires_at ?? now();
        $license->expires_at = $base->addDays($validated['days']);
        $license->save();

        return back()->with('success', 'Lisans süresi ' . $validated['days'] . ' gün uzatıldı.');
    }
}
