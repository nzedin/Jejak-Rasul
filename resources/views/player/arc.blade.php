@extends('player.layout')

@section('title', $arc->title.' — Jejak Rasul')

@section('content')
    <section class="arc-head" data-arc-start="{{ $arc->slug }}">
        <p class="eyebrow">Arka</p>
        <h1>{{ $arc->title }}</h1>
        @if ($arc->description)<p class="lead">{{ $arc->description }}</p>@endif
        <p class="muted" data-progress-text data-arc="{{ $arc->slug }}" data-total="{{ $chapters->count() }}"></p>
    </section>

    @if ($chapters->isEmpty())
        <section class="empty">
            <h2>Belum ada bab yang diterbitkan</h2>
            <p>Bab akan muncul di sini selepas disemak.</p>
        </section>
    @else
        <ol class="journey" aria-label="Peta perjalanan">
            @foreach ($chapters as $i => $chapter)
                <li class="journey-stop"
                    data-chapter="{{ $chapter->id }}"
                    data-unlock="{{ $preview ? 0 : $chapter->unlock_day_offset }}">
                    <span class="journey-lamp" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 2c1.5 2.2 2.5 3.7 2.5 5.2A2.5 2.5 0 0 1 12 9.7a2.5 2.5 0 0 1-2.5-2.5C9.5 5.7 10.5 4.2 12 2Z"/><path d="M5 12h14l-1.6 5.2A3 3 0 0 1 14.5 19.5h-5a3 3 0 0 1-2.9-2.3L5 12Z"/></svg>
                    </span>
                    <a class="journey-link" href="{{ route('player.chapter', ['arc' => $arc->slug, 'chapter' => $chapter->id] + ($preview ? ['pratonton' => 1] : [])) }}">
                        <span class="journey-number">Bab {{ $i + 1 }}</span>
                        <span class="journey-title">{{ $chapter->revision?->title }}</span>
                        @if ($preview && $chapter->revision?->status)
                            <span class="status status-{{ $chapter->revision->status->value }}">{{ $chapter->revision->status->getLabel() }}</span>
                        @endif
                        <span class="journey-lock" data-lock-text hidden></span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
@endsection
