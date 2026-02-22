<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LisansYönetim') - SaaS Lisans Sistemi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">

<nav class="bg-indigo-700 text-white px-6 py-3 flex items-center justify-between shadow">
    <div class="flex items-center gap-6">
        <span class="font-bold text-lg">🔑 SaaS Lisans</span>
        @auth
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="hover:underline text-sm">Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="hover:underline text-sm">Kullanıcılar</a>
                <a href="{{ route('admin.licenses.index') }}" class="hover:underline text-sm">Lisanslar</a>
                <a href="{{ route('admin.logs.index') }}" class="hover:underline text-sm">Loglar</a>
                <a href="{{ route('admin.blacklist.index') }}" class="hover:underline text-sm">Kara Liste</a>
            @else
                <a href="{{ route('user.dashboard') }}" class="hover:underline text-sm">Lisanslarım</a>
                <a href="{{ route('user.password.edit') }}" class="hover:underline text-sm">Şifre Değiştir</a>
            @endif
        @endauth
    </div>
    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm bg-indigo-800 hover:bg-indigo-900 px-3 py-1 rounded">Çıkış</button>
        </form>
    @endauth
</nav>

<main class="container mx-auto px-4 py-6 max-w-6xl">
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif
    @yield('content')
</main>

</body>
</html>
