<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceBlacklist;
use Illuminate\Http\Request;

class DeviceBlacklistController extends Controller
{
    public function index()
    {
        $devices = DeviceBlacklist::latest()->paginate(30);

        return view('admin.blacklist.index', compact('devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:128|unique:device_blacklists,device_id',
            'reason'    => 'nullable|string|max:500',
        ]);

        DeviceBlacklist::create($validated);

        return redirect()->route('admin.blacklist.index')->with('success', 'Cihaz kara listeye eklendi.');
    }

    public function destroy(DeviceBlacklist $blacklist)
    {
        $blacklist->delete();

        return redirect()->route('admin.blacklist.index')->with('success', 'Cihaz kara listeden kaldırıldı.');
    }
}
