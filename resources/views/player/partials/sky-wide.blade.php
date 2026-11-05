{{-- Langit malam padang pasir versi landskap (skrin komputer & panel). --}}
@php
    mt_srand(4242);
    $stars = [];
    for ($i = 0; $i < 140; $i++) {
        $stars[] = [mt_rand(0, 1600), mt_rand(0, 640), mt_rand(5, 14) / 10, mt_rand(0, 40) / 10, mt_rand(20, 50) / 10];
    }
@endphp
<svg class="bg {{ $class ?? '' }}" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMax slice" focusable="false">
        <defs>
            <linearGradient id="jr-sky-grad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#060b18"/><stop offset=".65" stop-color="#14224a"/><stop offset="1" stop-color="#3a2d55"/>
            </linearGradient>
            <radialGradient id="jr-moon-glow">
                <stop offset="0" stop-color="#fff6d8" stop-opacity=".8"/><stop offset="1" stop-color="#fff6d8" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <rect width="1600" height="900" fill="url(#jr-sky-grad)"/>
        <g class="stars">
            @foreach ($stars as [$x, $y, $r, $delay, $dur])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}" fill="#fff" style="animation-delay: {{ $delay }}s; animation-duration: {{ $dur }}s"/>
            @endforeach
        </g>
        <g class="moon"><circle cx="1380" cy="140" r="90" fill="url(#jr-moon-glow)"/><circle cx="1380" cy="140" r="24" fill="#fdf3d0"/><circle cx="1391" cy="133" r="21" fill="#0b1430"/></g>
        <line class="shooting-star" x1="1100" y1="80" x2="1040" y2="116" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
        <path class="layer layer-far" d="M-100 760 Q200 700 500 740 T1100 730 T1700 745 L1700 900 L-100 900Z" fill="#2b2547"/>
        <g transform="translate(0 205)"><g class="caravan caravan-wide" fill="#14102a">
            @foreach ([0, 34, 68, 102] as $cx)
                <g transform="translate({{ $cx }} 0)"><path d="M20 548 q4 -14 14 -12 q6 -10 14 0 q8 -2 10 6 l-3 1 l0 14 l-3 0 l0 -10 l-14 0 l0 10 l-3 0 l0 -10 q-8 -2 -12 -6 l-4 -4 q-6 -4 -9 2 l-3 -2 q4 -8 12 -4z"/></g>
            @endforeach
        </g></g>
        <path class="layer layer-mid" d="M-100 820 Q300 770 700 815 T1700 800 L1700 900 L-100 900Z" fill="#1d1836"/>
        <path class="layer layer-near" d="M-100 870 Q400 835 800 865 T1700 860 L1700 900 L-100 900Z" fill="#120f24"/>
    </svg>
