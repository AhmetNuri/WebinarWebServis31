@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-indigo-600">{{ $totalUsers }}</div>
        <div class="text-sm text-gray-500 mt-1">Toplam Kullanıcı</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-blue-600">{{ $totalLicenses }}</div>
        <div class="text-sm text-gray-500 mt-1">Toplam Lisans</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-green-600">{{ $activeLicenses }}</div>
        <div class="text-sm text-gray-500 mt-1">Aktif Lisans</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-red-600">{{ $expiredLicenses }}</div>
        <div class="text-sm text-gray-500 mt-1">Süresi Dolmuş</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow p-5">
    <h2 class="font-semibold text-lg mb-3">Son Log Kayıtları</h2>
    @if($recentLogs->isEmpty())
        <p class="text-gray-500 text-sm">Henüz log kaydı yok.</p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">
                    <th class="pb-2">Zaman</th>
                    <th class="pb-2">Seviye</th>
                    <th class="pb-2">Olay</th>
                    <th class="pb-2">Mesaj</th>
                    <th class="pb-2">Kullanıcı</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLogs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 text-gray-400">{{ $log->created_at->format('d.m H:i') }}</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                {{ $log->level === 'error' ? 'bg-red-100 text-red-700' : ($log->level === 'debug' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                {{ $log->level }}
                            </span>
                        </td>
                        <td class="py-2">{{ $log->event }}</td>
                        <td class="py-2 truncate max-w-xs">{{ $log->message }}</td>
                        <td class="py-2 text-gray-500">{{ $log->user?->email ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            <a href="{{ route('admin.logs.index') }}" class="text-indigo-600 text-sm hover:underline">Tüm logları gör →</a>
        </div>
    @endif
</div>
@endsection
