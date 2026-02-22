@extends('layouts.app')
@section('title', 'Şifre Değiştir')
@section('content')
<div class="max-w-md">
    <h1 class="text-2xl font-bold mb-6">Şifre Değiştir</h1>

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
        <form method="POST" action="{{ route('user.password.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Mevcut Şifre</label>
                <input type="password" name="current_password" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Yeni Şifre</label>
                <input type="password" name="password" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Yeni Şifre Tekrar</label>
                <input type="password" name="password_confirmation" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded text-sm font-medium">Şifreyi Güncelle</button>
        </form>
    </div>
</div>
@endsection
