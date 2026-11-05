@extends('player.layout')

@section('body-class', 'page-home')

@section('content')
    <section class="hero">
        <p class="eyebrow">Kembara sirah interaktif</p>
        <h1>Ikuti jejak perjalanan Rasulullah ﷺ, satu bab sehari.</h1>
        <p class="lead">
            Anda ialah <strong>Yusuf</strong>, anak seorang pedagang kecil di Makkah — watak rekaan
            yang menyaksikan peristiwa sebenar dari dekat. Setiap babak disertakan sumber, supaya anda tahu
            mana yang sahih, mana yang riwayat, dan mana yang sekadar gambaran suasana.
        </p>
    </section>

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
                <span class="arc-progress" data-progress-text>{{ $count }} bab</span>
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
@endsection
