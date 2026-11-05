@extends('player.layout')

@section('body-class', 'page-home')

@section('content')
    <section class="home-hero">
        <div class="only-portrait">@include('player.partials.scene-bg', ['setting' => 'makkah_malam', 'seed' => 11])</div>
        <div class="only-landscape">@include('player.partials.sky-wide')</div>
        <div class="home-hero-inner">
            @include('player.partials.emblem', ['class' => 'hero-emblem'])
            <p class="eyebrow">Kembara sirah interaktif</p>
            <h1>Jejak Sirah</h1>
            <p class="lead">
                Makkah, suatu malam. Kau ialah <strong>Yusuf</strong>, anak seorang pedagang kecil.
                Malam ini, kau akan menyaksikan sebuah perjalanan yang mengubah sejarah.
            </p>
            <p class="fineprint">Yusuf ialah watak rekaan. Setiap peristiwa disertakan sumber dan label kesahihan.</p>
        </div>
    </section>

    <section class="home-arcs">
        @forelse ($arcs as $arc)
            @php $count = $arc->visibleChapters->count(); @endphp
            <a class="arc-card" href="{{ route('player.arc', ['arc' => $arc->slug] + ($preview ? ['pratonton' => 1] : [])) }}"
               data-arc="{{ $arc->slug }}" data-total="{{ $count }}">
                <div class="arc-card-body">
                    <p class="eyebrow">Arka</p>
                    <h2>{{ $arc->title }}</h2>
                    @if ($arc->description)<p>{{ $arc->description }}</p>@endif
                </div>
                <div class="arc-card-meta">
                    <div class="lamps" aria-hidden="true">
                        @for ($i = 0; $i < min($count, 10); $i++)<span class="lamp-dot"></span>@endfor
                    </div>
                    <span class="arc-cta" data-progress-text>Mula kembara →</span>
                </div>
            </a>
        @empty
            <section class="empty">
                <h2>Kembara pertama sedang disediakan</h2>
                <p>Kandungan sedang ditulis dan disemak oleh penyemak bertauliah. Ia akan muncul di sini sebaik sahaja diluluskan.</p>
                @auth
                    @if (auth()->user()->isStaff() && ! $preview)
                        <p><a class="button" href="{{ route('player.home', ['pratonton' => 1]) }}">Lihat pratonton draf</a></p>
                    @endif
                @endauth
            </section>
        @endforelse
    </section>
@endsection
