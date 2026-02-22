@extends('layouts.app')
@section('title', 'Kullanıcılar')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Kullanıcılar</h1>
    <a href="{{ route('admin.users.create') }}"
        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded">+ Yeni Kullanıcı</a>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ $search }}" placeholder="Ad veya e-posta ile ara..."
        class="border border-gray-300 rounded px-3 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm">Ara</button>
    @if($search)
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Temizle</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3">Ad</th>
                <th class="text-left px-4 py-3">E-posta</th>
                <th class="text-left px-4 py-3">Lisans Sayısı</th>
                <th class="text-left px-4 py-3">Kayıt Tarihi</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.licenses.index', ['search' => $user->email]) }}"
                            class="text-indigo-600 hover:underline">{{ $user->licenses_count }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $user->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3 flex gap-2 justify-end">
                        <a href="{{ route('admin.licenses.create', ['user_id' => $user->id]) }}"
                            class="text-green-600 hover:underline text-xs">+ Lisans</a>
                        <a href="{{ route('admin.users.edit', $user) }}"
                            class="text-blue-600 hover:underline text-xs">Düzenle</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                            onsubmit="return confirm('Kullanıcıyı silmek istediğinize emin misiniz?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-xs">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">Kullanıcı bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3">{{ $users->links() }}</div>
</div>
@endsection
