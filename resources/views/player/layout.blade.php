<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Jejak Rasul')</title>
    <meta name="description" content="Kembara sirah interaktif dengan sumber yang disemak.">
    <meta name="theme-color" content="#0d1726">
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
            <svg class="brand-lamp" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2c1.5 2.2 2.5 3.7 2.5 5.2A2.5 2.5 0 0 1 12 9.7a2.5 2.5 0 0 1-2.5-2.5C9.5 5.7 10.5 4.2 12 2Z"/><path d="M5 12h14l-1.6 5.2A3 3 0 0 1 14.5 19.5h-5a3 3 0 0 1-2.9-2.3L5 12Z"/><path d="M9 21.5h6"/></svg>
            <span>Jejak Rasul</span>
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
