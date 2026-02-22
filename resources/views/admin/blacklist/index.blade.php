@extends('layouts.app')
@section('title', 'Cihaz Kara Listesi')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Cihaz Kara Listesi</h1>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold mb-4">Cihaz Ekle</h2>
        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.blacklist.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Cihaz ID</label>
                <input type="text" name="device_id" value="{{ old('device_id') }}" required maxlength="128"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Neden <span class="text-gray-400">(opsiyonel)</span></label>
                <textarea name="reason" rows="2" maxlength="500"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('reason') }}</textarea>
            </div>
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">Kara Listeye Ekle</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Cihaz ID</th>
                    <th class="text-left px-4 py-3">Neden</th>
                    <th class="text-left px-4 py-3">Tarih</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $device->device_id }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $device->reason ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $device->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.blacklist.destroy', $device) }}"
                                onsubmit="return confirm('Cihazı kara listeden kaldırmak istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Kaldır</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">Kara listede cihaz yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $devices->links() }}</div>
    </div>
</div>
@endsection
