@extends('layouts.app')
@section('title', 'Lisanslar')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Lisanslar</h1>
    <a href="{{ route('admin.licenses.create') }}"
        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded">+ Yeni Lisans</a>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ $search }}" placeholder="Seri no veya kullanıcı ile ara..."
        class="border border-gray-300 rounded px-3 py-2 text-sm w-80 focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">Ara</button>
    @if($search)
        <a href="{{ route('admin.licenses.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Temizle</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow overflow-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3">Kullanıcı</th>
                <th class="text-left px-4 py-3">Seri No</th>
                <th class="text-left px-4 py-3">Paket</th>
                <th class="text-left px-4 py-3">Tip</th>
                <th class="text-left px-4 py-3">Bitiş</th>
                <th class="text-left px-4 py-3">Durum</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($licenses as $license)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $license->user?->name }}</div>
                        <div class="text-gray-400 text-xs">{{ $license->user?->email }}</div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $license->serial_number }}</td>
                    <td class="px-4 py-3">{{ $license->product_package }}</td>
                    <td class="px-4 py-3">{{ $license->license_type }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        @if($license->license_type === 'lifetime')
                            <span class="text-green-600 font-medium">Sınırsız</span>
                        @elseif($license->expires_at)
                            {{ $license->expires_at->format('d.m.Y') }}
                            @php $days = $license->daysLeft(); @endphp
                            @if($days !== null && $days < 10)
                                <span class="text-xs text-red-500">({{ $days }} gün)</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(!$license->user_enable)
                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">Askıda</span>
                        @elseif($license->isActive())
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">Aktif</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">Süresi Doldu</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 flex gap-2 justify-end">
                        <a href="{{ route('admin.licenses.edit', $license) }}"
                            class="text-blue-600 hover:underline text-xs">Düzenle</a>
                        <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}"
                            onsubmit="return confirm('Lisansı silmek istediğinize emin misiniz?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-xs">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">Lisans bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3">{{ $licenses->links() }}</div>
</div>
@endsection
