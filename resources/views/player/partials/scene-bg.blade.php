{{--
    Latar beranimasi untuk setiap babak (landskap & objek sahaja).
    $setting: nilai App\Enums\SceneSetting
    $seed:    nombor supaya kedudukan bintang berbeza antara babak
--}}
@php
    $setting = $setting ?? 'gurun_malam';
    $night = in_array($setting, ['makkah_malam', 'gurun_malam', 'gua'], true);
    mt_srand(($seed ?? 1) * 7919);
    $stars = [];
    for ($i = 0; $i < 70; $i++) {
        $stars[] = [mt_rand(0, 400), mt_rand(0, 430), mt_rand(5, 16) / 10, mt_rand(0, 40) / 10, mt_rand(20, 50) / 10];
    }
@endphp
<svg class="bg bg-{{ $setting }}" viewBox="0 0 400 800" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="sky-{{ $setting }}" x1="0" y1="0" x2="0" y2="1">
            @switch($setting)
                @case('gurun_siang')
                    <stop offset="0" stop-color="#7cb7e0"/><stop offset=".55" stop-color="#f3d9a8"/><stop offset="1" stop-color="#f0b46a"/>
                    @break
                @case('pesisir')
                    <stop offset="0" stop-color="#2b3a67"/><stop offset=".5" stop-color="#c46f6a"/><stop offset="1" stop-color="#f2b880"/>
                    @break
                @case('oasis')
                    <stop offset="0" stop-color="#3b4a7a"/><stop offset=".45" stop-color="#e59a8c"/><stop offset=".8" stop-color="#f6d08a"/><stop offset="1" stop-color="#f9e2b0"/>
                    @break
                @default
                    <stop offset="0" stop-color="#060b18"/><stop offset=".6" stop-color="#14224a"/><stop offset="1" stop-color="#3a2d55"/>
            @endswitch
        </linearGradient>
        <radialGradient id="glow-{{ $setting }}">
            <stop offset="0" stop-color="#fff6d8" stop-opacity=".9"/>
            <stop offset="1" stop-color="#fff6d8" stop-opacity="0"/>
        </radialGradient>
    </defs>

    <rect width="400" height="800" fill="url(#sky-{{ $setting }})"/>

    @if ($night || $setting === 'pesisir' || $setting === 'oasis')
        <g class="stars {{ $night ? '' : 'stars-fading' }}">
            @foreach ($stars as [$x, $y, $r, $delay, $dur])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}" fill="#fff" style="animation-delay: {{ $delay }}s; animation-duration: {{ $dur }}s"/>
            @endforeach
        </g>
    @endif

    @switch($setting)
        @case('makkah_malam')
            <g class="moon"><circle cx="300" cy="140" r="70" fill="url(#glow-{{ $setting }})"/><circle cx="300" cy="140" r="22" fill="#fdf3d0"/><circle cx="310" cy="134" r="20" fill="#0b1430"/></g>
            <path class="layer layer-far" d="M0 520 L60 430 L120 500 L190 400 L260 490 L330 420 L400 480 L400 800 L0 800Z" fill="#262e52"/>
            <path class="layer layer-mid" d="M0 600 L80 520 L150 580 L230 510 L310 570 L400 530 L400 800 L0 800Z" fill="#151a30"/>
            <g class="houses" fill="#1a1d36">
                <rect x="10" y="610" width="70" height="200"/><rect x="70" y="640" width="60" height="170"/><rect x="125" y="600" width="80" height="210"/>
                <rect x="200" y="630" width="55" height="180"/><rect x="250" y="590" width="75" height="220"/><rect x="320" y="625" width="80" height="185"/>
            </g>
            <g class="windows" fill="#ffc46b">
                <rect x="30" y="640" width="8" height="10" style="animation-delay:.2s"/><rect x="150" y="625" width="8" height="10" style="animation-delay:1.4s"/>
                <rect x="270" y="615" width="8" height="10" style="animation-delay:2.1s"/><rect x="345" y="650" width="8" height="10" style="animation-delay:.8s"/>
                <rect x="95" y="670" width="8" height="10" style="animation-delay:3s"/>
            </g>
            @break

        @case('gua')
            <g class="moon"><circle cx="200" cy="300" r="90" fill="url(#glow-{{ $setting }})"/><circle cx="200" cy="300" r="16" fill="#fdf3d0"/></g>
            <path class="layer layer-mid" d="M0 640 Q100 560 200 600 T400 580 L400 800 L0 800Z" fill="#1a1630"/>
            <path class="cave" fill="#07060c" fill-rule="evenodd"
                  d="M0 0 H400 V800 H0 Z M70 760 C40 600 60 380 120 250 C170 150 250 140 300 230 C360 340 370 560 330 760 Z"/>
            <path class="cave-edge" fill="none" stroke="#2a2340" stroke-width="6"
                  d="M70 760 C40 600 60 380 120 250 C170 150 250 140 300 230 C360 340 370 560 330 760"/>
            <ellipse class="moonlight" cx="200" cy="760" rx="150" ry="30" fill="#b9c6ff" opacity=".12"/>
            @break

        @case('gurun_siang')
            <g class="sun"><circle cx="290" cy="220" r="120" fill="url(#glow-{{ $setting }})"/><circle cx="290" cy="220" r="40" fill="#fff4cf"/></g>
            <g class="birds" fill="none" stroke="#5b4630" stroke-width="2" stroke-linecap="round">
                <path d="M60 180 q8 -8 16 0 q8 -8 16 0"/><path d="M100 150 q6 -6 12 0 q6 -6 12 0"/>
            </g>
            <path class="layer layer-far" d="M-50 560 Q60 480 170 540 T390 520 T600 540 L600 800 L-50 800Z" fill="#e7b16c"/>
            <path class="layer layer-mid" d="M-50 640 Q90 560 230 630 T520 610 L520 800 L-50 800Z" fill="#d89650"/>
            <path class="layer layer-near" d="M-50 720 Q120 650 260 720 T560 700 L560 800 L-50 800Z" fill="#c07d3c"/>
            <g class="dust" fill="#fff3d6">
                @for ($i = 0; $i < 14; $i++)
                    <circle cx="{{ mt_rand(0, 400) }}" cy="{{ mt_rand(560, 780) }}" r="{{ mt_rand(6, 16) / 10 }}" style="animation-delay: {{ mt_rand(0, 60) / 10 }}s"/>
                @endfor
            </g>
            @break

        @case('pesisir')
            <g class="sun"><circle cx="120" cy="470" r="110" fill="url(#glow-{{ $setting }})"/><circle cx="120" cy="470" r="34" fill="#ffe2b3"/></g>
            <path class="layer layer-far" d="M0 470 L90 430 L170 460 L260 420 L400 455 L400 500 L0 500Z" fill="#6b4a63"/>
            <rect y="495" width="400" height="200" fill="#3d4f7c"/>
            <g class="waves" fill="none" stroke="#f6c9a0" stroke-opacity=".5" stroke-width="2" stroke-linecap="round">
                <path d="M-40 530 q20 -6 40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0"/>
                <path d="M-40 570 q20 -6 40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0" style="animation-delay:-2s"/>
                <path d="M-40 615 q20 -6 40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0 t40 0" style="animation-delay:-4s"/>
            </g>
            <path class="layer layer-near" d="M0 690 Q140 650 260 680 T400 670 L400 800 L0 800Z" fill="#c99467"/>
            @break

        @case('oasis')
            <g class="sun sun-rise"><circle cx="200" cy="560" r="140" fill="url(#glow-{{ $setting }})"/><circle cx="200" cy="560" r="42" fill="#fff0c8"/></g>
            <g class="houses houses-far" fill="#a46b63">
                <rect x="40" y="560" width="50" height="60"/><rect x="85" y="545" width="40" height="75"/><rect x="270" y="555" width="55" height="65"/><rect x="320" y="540" width="45" height="80"/>
            </g>
            <path class="layer layer-mid" d="M0 610 Q120 580 220 600 T400 590 L400 800 L0 800Z" fill="#5d6b3f"/>
            @foreach ([[60, 640, 1], [340, 650, -1], [120, 700, 1]] as [$px, $py, $dir])
                <g class="palm" style="transform-origin: {{ $px }}px {{ $py }}px; animation-delay: {{ $px / 100 }}s">
                    <path d="M{{ $px }} {{ $py + 160 }} Q{{ $px + 6 * $dir }} {{ $py + 60 }} {{ $px }} {{ $py }}" stroke="#3a2a1c" stroke-width="7" fill="none"/>
                    <g fill="#2f4a26">
                        <path d="M{{ $px }} {{ $py }} q-40 -10 -70 20 q35 -30 70 -20Z"/>
                        <path d="M{{ $px }} {{ $py }} q40 -10 70 20 q-35 -30 -70 -20Z"/>
                        <path d="M{{ $px }} {{ $py }} q-25 -35 -55 -35 q35 0 55 35Z"/>
                        <path d="M{{ $px }} {{ $py }} q25 -35 55 -35 q-35 0 -55 35Z"/>
                    </g>
                </g>
            @endforeach
            <path class="layer layer-near" d="M0 740 Q150 700 260 735 T400 725 L400 800 L0 800Z" fill="#3f4a2b"/>
            @break

        @default {{-- gurun_malam --}}
            <g class="moon"><circle cx="90" cy="160" r="80" fill="url(#glow-{{ $setting }})"/><circle cx="90" cy="160" r="20" fill="#fdf3d0"/><circle cx="99" cy="154" r="18" fill="#0b1430"/></g>
            <line class="shooting-star" x1="380" y1="60" x2="330" y2="90" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
            <path class="layer layer-far" d="M-50 560 Q60 500 170 545 T390 530 T600 545 L600 800 L-50 800Z" fill="#2b2547"/>
            <g class="caravan" fill="#14102a">
                @foreach ([0, 34, 68] as $cx)
                    <g transform="translate({{ $cx }} 0)">
                        <path d="M20 548 q4 -14 14 -12 q6 -10 14 0 q8 -2 10 6 l-3 1 l0 14 l-3 0 l0 -10 l-14 0 l0 10 l-3 0 l0 -10 q-8 -2 -12 -6 l-4 -4 q-6 -4 -9 2 l-3 -2 q4 -8 12 -4z"/>
                    </g>
                @endforeach
            </g>
            <path class="layer layer-mid" d="M-50 640 Q90 580 230 640 T520 620 L520 800 L-50 800Z" fill="#1d1836"/>
            <path class="layer layer-near" d="M-50 720 Q120 670 260 720 T560 705 L560 800 L-50 800Z" fill="#120f24"/>
    @endswitch
</svg>
