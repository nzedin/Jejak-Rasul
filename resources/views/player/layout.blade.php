<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Jejak Sirah')</title>
    <meta name="description" content="Kembara sirah interaktif dengan sumber yang disemak.">
    <meta name="theme-color" content="#0d1726">
    <script>document.documentElement.classList.add('js')</script>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/jejak.css') }}">
</head>
<body class="@yield('body-class')">
    @if ($preview ?? false)
        <div class="preview-bar" role="status">
            <strong>Mod pratonton</strong> — anda melihat versi terkini termasuk <em>draf yang belum disemak</em>. Pemain hanya nampak kandungan yang diluluskan.
            <a href="{{ url()->current() }}">Keluar pratonton</a>
        </div>
    @endif

    <header class="site-header">
        <a href="{{ route('player.home', ($preview ?? false) ? ['pratonton' => 1] : []) }}" class="brand">
            @include('player.partials.emblem', ['class' => 'brand-emblem'])
            <span>Jejak Sirah</span>
        </a>
        @yield('header-extra')
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <p>Setiap babak ditanda dengan label kesahihan dan disemak sebelum diterbitkan.</p>
        <p class="legend">
            <span class="legend-item"><span class="label label-sahih">Sahih</span> hadis sahih / Al-Quran</span>
            <span class="legend-item"><span class="label label-riwayat">Riwayat</span> kitab sirah muktabar</span>
            <span class="legend-item"><span class="label label-rekaan">Rekaan</span> watak rekaan & suasana</span>
        </p>
    </footer>

    <script src="{{ asset('js/jejak.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
