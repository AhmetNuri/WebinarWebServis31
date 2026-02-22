@extends('layouts.app')
@section('title', 'Lisans Düzenle')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Lisans Düzenle</h1>

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6 space-y-6">
        <form method="POST" action="{{ route('admin.licenses.update', $license) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Kullanıcı</label>
                <select name="user_id" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $license->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Seri Numara</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $license->serial_number) }}" required maxlength="64"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Cihaz ID <span class="text-gray-400">(kaldırmak için boş bırakın)</span></label>
                <input type="text" name="device_id" value="{{ old('device_id', $license->device_id) }}" maxlength="128"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $license->starts_at?->format('Y-m-d\TH:i')) }}" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bitiş Tarihi</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $license->expires_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Lisans Tipi</label>
                    <select name="license_type" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(['demo', 'monthly', 'yearly', 'lifetime'] as $type)
                            <option value="{{ $type }}" {{ old('license_type', $license->license_type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ürün Paketi</label>
                    <input type="text" name="product_package" value="{{ old('product_package', $license->product_package) }}" required maxlength="64"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Max Bağlantı Sayısı</label>
                <input type="number" name="max_connection_count" value="{{ old('max_connection_count', $license->max_connection_count) }}" min="1" required
                    class="w-40 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="user_enable" value="1" {{ old('user_enable', $license->user_enable) ? 'checked' : '' }}>
                    Lisans Aktif
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="emergency" value="1" {{ old('emergency', $license->emergency) ? 'checked' : '' }}>
                    Acil Mod
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium">Güncelle</button>
                <a href="{{ route('admin.licenses.index') }}" class="text-gray-600 hover:underline text-sm py-2">İptal</a>
            </div>
        </form>

        <hr>

        <div>
            <h3 class="font-semibold mb-3 text-gray-700">⏱ Süre Uzat</h3>
            <form method="POST" action="{{ route('admin.licenses.extend', $license) }}" class="flex items-center gap-3">
                @csrf
                <input type="number" name="days" min="1" max="3650" placeholder="Gün sayısı" required
                    class="w-36 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium">Uzat</button>
            </form>
            @if($license->expires_at)
                <p class="text-xs text-gray-400 mt-1">Mevcut bitiş: {{ $license->expires_at->format('d.m.Y H:i') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
