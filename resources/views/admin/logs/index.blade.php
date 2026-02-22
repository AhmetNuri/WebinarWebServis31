@extends('layouts.app')
@section('title', 'Log Kayıtları')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Log Kayıtları</h1>
</div>

<form method="GET" class="mb-4 flex gap-2 flex-wrap">
    <select name="level"
        class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <option value="">-- Tüm Seviyeler --</option>
        @foreach(['info', 'debug', 'error'] as $lvl)
            <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
        @endforeach
    </select>
    <input type="text" name="event" value="{{ $event }}" placeholder="Olay adı..."
        class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">Filtrele</button>
    <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Temizle</a>
</form>

<div class="bg-white rounded-xl shadow overflow-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3">Zaman</th>
                <th class="text-left px-4 py-3">Seviye</th>
                <th class="text-left px-4 py-3">Olay</th>
                <th class="text-left px-4 py-3">Mesaj</th>
                <th class="text-left px-4 py-3">Kullanıcı</th>
                <th class="text-left px-4 py-3">Cihaz</th>
                <th class="text-left px-4 py-3">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $log->level === 'error' ? 'bg-red-100 text-red-700' : ($log->level === 'debug' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            {{ $log->level }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $log->event }}</td>
                    <td class="px-4 py-3 max-w-xs truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->user?->email ?? '-' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-400 truncate max-w-xs">{{ $log->device_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $log->ip_address ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">Log kaydı bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3">{{ $logs->links() }}</div>
</div>
@endsection
