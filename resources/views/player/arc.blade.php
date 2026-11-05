@extends('player.layout')

@section('title', $arc->title.' — Jejak Sirah')
@section('body-class', 'page-map')

@php
    $n = $chapters->count();
    $gap = 130;
    $height = max(1, $n) * $gap + 60;
    $pts = $chapters->values()->map(fn ($c, $i) => [$i % 2 === 0 ? 120 : 280, 70 + $i * $gap]);
    $d = '';
    foreach ($pts as $i => [$x, $y]) {
        if ($i === 0) { $d = "M{$x} {$y}"; continue; }
        [$px, $py] = $pts[$i - 1];
        $my = ($py + $y) / 2;
        $d .= " C{$px} {$my} {$x} {$my} {$x} {$y}";
    }
@endphp

@section('content')
    <div class="map-bg">
        <div class="only-portrait">@include('player.partials.scene-bg', ['setting' => 'gurun_malam', 'seed' => 3])</div>
        <div class="only-landscape">@include('player.partials.sky-wide')</div>
    </div>

    <section class="map-head" data-arc-start="{{ $arc->slug }}">
        <p class="eyebrow">Arka</p>
        <h1>{{ $arc->title }}</h1>
        @if ($arc->description)<p class="lead">{{ $arc->description }}</p>@endif
        <p class="map-progress" data-progress-text data-arc="{{ $arc->slug }}" data-total="{{ $n }}"></p>
    </section>

    @if ($chapters->isEmpty())
        <section class="empty empty-dark">
            <h2>Belum ada bab yang diterbitkan</h2>
            <p>Bab akan muncul di sini selepas disemak.</p>
        </section>
    @else
        <div class="map" style="--map-h: {{ $height }}px">
            <svg class="map-path" viewBox="0 0 400 {{ $height }}" preserveAspectRatio="none" aria-hidden="true">
                <path d="{{ $d }}" class="path-base" pathLength="100"/>
                <path d="{{ $d }}" class="path-done" pathLength="100" data-path-done/>
            </svg>

            <ol class="map-stops" aria-label="Peta perjalanan">
                @foreach ($chapters as $i => $chapter)
                    @php [$x, $y] = $pts[$i]; @endphp
                    <li class="stop {{ $i % 2 ? 'stop-right' : 'stop-left' }}"
                        style="left: {{ $x / 4 }}%; top: {{ $y }}px"
                        data-chapter="{{ $chapter->id }}"
                        data-index="{{ $i }}"
                        data-unlock="{{ $preview ? 0 : $chapter->unlock_day_offset }}">
                        <a class="stop-node" href="{{ route('player.chapter', ['arc' => $arc->slug, 'chapter' => $chapter->id] + ($preview ? ['pratonton' => 1] : [])) }}">
                            <span class="stop-lamp" aria-hidden="true">@include('player.partials.star')</span>
                            <span class="stop-label">
                                <span class="stop-number">Bab {{ $i + 1 }}</span>
                                <span class="stop-title">{{ $chapter->revision?->title }}</span>
                                @if ($preview && $chapter->revision?->status)
                                    <span class="status status-{{ $chapter->revision->status->value }}">{{ $chapter->revision->status->getLabel() }}</span>
                                @endif
                                <span class="stop-lock" data-lock-text hidden></span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
@endsection
