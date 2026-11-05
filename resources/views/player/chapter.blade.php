@extends('player.layout')

@php
    use App\Support\SafeHtml;
    $q = $preview ? ['pratonton' => 1] : [];
    $labelNames = ['sahih' => 'Sahih', 'riwayat' => 'Riwayat', 'rekaan' => 'Rekaan'];
@endphp

@section('title', ($chapter->revision?->title ?? 'Bab').' — Jejak Rasul')

@section('header-extra')
    <a class="back-link" href="{{ route('player.arc', ['arc' => $arc->slug] + $q) }}">← {{ $arc->title }}</a>
@endsection

@section('content')
<article class="chapter"
         data-arc="{{ $arc->slug }}"
         data-chapter="{{ $chapter->id }}"
         data-unlock="{{ $preview ? 0 : $chapter->unlock_day_offset }}">

    <header class="chapter-head">
        <p class="eyebrow">Bab {{ $number }} daripada {{ $total }}</p>
        <h1>{{ $chapter->revision?->title }}</h1>
        @if ($chapter->revision?->summary)<p class="lead">{{ $chapter->revision->summary }}</p>@endif
    </header>

    <div class="locked-panel" data-locked-panel hidden>
        <h2>Bab ini belum dibuka</h2>
        <p data-locked-text></p>
        <a class="button" href="{{ route('player.arc', ['arc' => $arc->slug] + $q) }}">Kembali ke peta</a>
    </div>

    <div data-chapter-body>
        {{-- 1. Cerita --}}
        @forelse ($scenes as $scene)
            @php $rev = $scene->revision; $label = $rev->label->value; @endphp
            <section class="scene scene-{{ $label }}">
                <div class="scene-meta">
                    <span class="label label-{{ $label }}">{{ $labelNames[$label] }}</span>
                    @if ($preview)
                        <span class="status status-{{ $rev->status->value }}">v{{ $rev->version }} · {{ $rev->status->getLabel() }}</span>
                    @endif
                </div>
                <div class="scene-body">{!! SafeHtml::clean($rev->body_ms) !!}</div>

                @if ($rev->sources->isNotEmpty())
                    <details class="sources">
                        <summary>Sumber ({{ $rev->sources->count() }})</summary>
                        <ul>
                            @foreach ($rev->sources as $source)
                                <li>
                                    <p class="source-title">
                                        {{ $source->title }}@if ($source->reference) — {{ $source->reference }}@endif
                                    </p>
                                    @if ($source->narrator || $source->grade)
                                        <p class="muted">
                                            @if ($source->narrator)Perawi: {{ $source->narrator }}@endif
                                            @if ($source->narrator && $source->grade) · @endif
                                            @if ($source->grade)Darjat: {{ $source->grade }}@endif
                                        </p>
                                    @endif
                                    @if ($source->text_ar)<p class="arabic" dir="rtl" lang="ar">{{ $source->text_ar }}</p>@endif
                                    @if ($source->text_ms)<p class="source-text">{{ $source->text_ms }}</p>@endif
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @elseif ($label !== 'rekaan')
                    <p class="muted small">Sumber belum dipautkan.</p>
                @endif
            </section>
        @empty
            <section class="empty">
                <p>Babak untuk bab ini sedang disemak.</p>
            </section>
        @endforelse

        {{-- 2. Pilihan Yusuf --}}
        @if ($choice)
            <section class="card choice" data-choice="{{ $chapter->id }}">
                <p class="eyebrow">Pilihan Yusuf</p>
                <h2>{{ $choice->prompt }}</h2>
                <div class="choice-options">
                    <button type="button" class="button button-outline" data-option="a">{{ $choice->option_a }}</button>
                    <button type="button" class="button button-outline" data-option="b">{{ $choice->option_b }}</button>
                </div>
                <p class="choice-outcome" data-outcome="a" hidden>{{ $choice->outcome_a }}</p>
                <p class="choice-outcome" data-outcome="b" hidden>{{ $choice->outcome_b }}</p>
                <p class="muted small">Pilihan anda hanya mengubah perjalanan Yusuf. Sejarah sebenar tidak berubah.</p>
            </section>
        @endif

        {{-- 3. Fakta atau Mitos? --}}
        @if ($quiz->isNotEmpty())
            <section class="card quiz">
                <p class="eyebrow">Fakta atau Mitos?</p>
                @foreach ($quiz as $question)
                    <div class="quiz-item" data-answer="{{ $question->answer->value }}">
                        <p class="quiz-statement">“{{ $question->statement }}”</p>
                        <div class="quiz-options">
                            <button type="button" class="button button-outline" data-guess="fakta">Fakta</button>
                            <button type="button" class="button button-outline" data-guess="lemah">Riwayat lemah</button>
                            <button type="button" class="button button-outline" data-guess="mitos">Mitos</button>
                        </div>
                        <div class="quiz-reveal" hidden>
                            <p class="quiz-verdict"><strong>Jawapan: {{ $question->answer->getLabel() }}</strong></p>
                            <p>{{ $question->explanation }}</p>
                            @if ($question->source)
                                <p class="muted small">Rujukan: {{ $question->source->display_name }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- 4. Nyalakan pelita + Kad Sumber + Jurnal Saksi --}}
        <section class="card finish" data-finish>
            <div class="finish-lamp" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 2c1.5 2.2 2.5 3.7 2.5 5.2A2.5 2.5 0 0 1 12 9.7a2.5 2.5 0 0 1-2.5-2.5C9.5 5.7 10.5 4.2 12 2Z"/><path d="M5 12h14l-1.6 5.2A3 3 0 0 1 14.5 19.5h-5a3 3 0 0 1-2.9-2.3L5 12Z"/><path d="M9 21.5h6"/></svg>
            </div>
            <h2 data-finish-title>Selesai membaca?</h2>
            <button type="button" class="button" data-complete>Nyalakan pelita bab ini</button>

            <div data-after-complete hidden>
                @if ($sources->isNotEmpty())
                    <h3>Kad Sumber dibuka</h3>
                    <div class="cards">
                        @foreach ($sources as $source)
                            <div class="source-card" data-card="{{ $source->id }}">
                                <span class="source-card-type">{{ $source->type->getLabel() }}</span>
                                <span class="source-card-title">{{ $source->title }}</span>
                                @if ($source->reference)<span class="source-card-ref">{{ $source->reference }}</span>@endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <h3>Jurnal Saksi</h3>
                <label for="journal" class="muted">Apa pengajaran yang anda bawa daripada bab ini?</label>
                <textarea id="journal" rows="3" data-journal placeholder="Tulis satu ayat…"></textarea>
                <p class="muted small" data-journal-saved hidden>Disimpan dalam peranti ini.</p>
            </div>
        </section>
    </div>

    <nav class="chapter-nav">
        @if ($previous)
            <a href="{{ route('player.chapter', ['arc' => $arc->slug, 'chapter' => $previous->id] + $q) }}">← {{ $previous->revision?->title }}</a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a href="{{ route('player.chapter', ['arc' => $arc->slug, 'chapter' => $next->id] + $q) }}" data-next>{{ $next->revision?->title }} →</a>
        @endif
    </nav>
</article>
@endsection
