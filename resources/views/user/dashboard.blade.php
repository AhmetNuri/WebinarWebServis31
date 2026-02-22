@extends('layouts.app')
@section('title', 'Lisanslarım')
@section('content')
<h1 class="text-2xl font-bold mb-6">Lisanslarım</h1>

@if($licenses->isEmpty())
    <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
        <p class="text-lg">Henüz lisansınız bulunmuyor.</p>
        <p class="text-sm mt-2">Lisans için yöneticinizle iletişime geçin.</p>
    </div>
@else
    <div class="grid gap-4">
        @foreach($licenses as $license)
            @php $days = $license->daysLeft(); @endphp
            <div class="bg-white rounded-xl shadow p-5 border-l-4
                {{ !$license->user_enable ? 'border-gray-400' : ($license->isActive() ? ($days !== null && $days < 10 ? 'border-yellow-400' : 'border-green-400') : 'border-red-400') }}">
                <div class="flex items-start justify-between flex-wrap gap-3">
                    <div>
                        <div class="font-mono text-sm text-gray-500 mb-1">{{ $license->serial_number }}</div>
                        <div class="font-bold text-lg">{{ $license->product_package }}</div>
                        <div class="text-sm text-gray-500">{{ ucfirst($license->license_type) }} Lisans</div>
                    </div>
                    <div class="text-right">
                        @if(!$license->user_enable)
                            <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">⏸ Askıda</span>
                        @elseif($license->isActive())
                            <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">✓ Aktif</span>
                        @else
                            <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">✗ Süresi Doldu</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t flex flex-wrap gap-6 text-sm">
                    <div>
                        <span class="text-gray-400">Başlangıç:</span>
                        <span class="ml-1">{{ $license->starts_at->format('d.m.Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Bitiş:</span>
                        <span class="ml-1">
                            @if($license->license_type === 'lifetime')
                                <span class="text-green-600 font-medium">Sınırsız</span>
                            @elseif($license->expires_at)
                                {{ $license->expires_at->format('d.m.Y') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    @if($days !== null)
                        <div>
                            <span class="text-gray-400">Kalan Gün:</span>
                            <span class="ml-1 font-medium {{ $days < 10 ? 'text-red-600' : '' }}">{{ $days }} gün</span>
                        </div>
                    @endif
                </div>

                @if($days !== null && $days < 10 && $license->isActive())
                    <div class="mt-3 bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 rounded text-sm">
                        ⚠️ Lisansınızın bitmesine 10 günden az kaldı! Lütfen yöneticinizle iletişime geçin.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
