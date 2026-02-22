@extends('layouts.app')
@section('title', 'Yeni Lisans')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Yeni Lisans Oluştur</h1>

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('admin.licenses.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Kullanıcı</label>
                <select name="user_id" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">-- Kullanıcı Seçin --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $selectedUserId) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Seri Numara <span class="text-gray-400">(boş bırakırsanız otomatik oluşturulur)</span></label>
                <input type="text" name="serial_number" value="{{ old('serial_number') }}" maxlength="64"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bitiş Tarihi <span class="text-gray-400">(lifetime için boş)</span></label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Lisans Tipi</label>
                    <select name="license_type" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(['demo', 'monthly', 'yearly', 'lifetime'] as $type)
                            <option value="{{ $type }}" {{ old('license_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ürün Paketi</label>
                    <input type="text" name="product_package" value="{{ old('product_package', 'Basic') }}" required maxlength="64"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Max Bağlantı Sayısı</label>
                <input type="number" name="max_connection_count" value="{{ old('max_connection_count', 1) }}" min="1" required
                    class="w-40 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="user_enable" value="1" {{ old('user_enable', '1') ? 'checked' : '' }}>
                    Lisans Aktif
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="emergency" value="1" {{ old('emergency') ? 'checked' : '' }}>
                    Acil Mod
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium">Oluştur</button>
                <a href="{{ route('admin.licenses.index') }}" class="text-gray-600 hover:underline text-sm py-2">İptal</a>
            </div>
        </form>
    </div>
</div>
@endsection
