@extends('player.layout')

@php
    use App\Support\SafeHtml;
    $q = $preview ? ['pratonton' => 1] : [];
    $labelNames = ['sahih' => 'Sahih', 'riwayat' => 'Riwayat', 'rekaan' => 'Rekaan'];
    $firstSetting = $scenes->first()?->setting?->value ?? 'gurun_malam';
    $lastSetting = $scenes->last()?->setting?->value ?? 'gurun_malam';
    $mapUrl = route('player.arc', ['arc' => $arc->slug] + $q);
@endphp

@section('title', ($chapter->revision?->title ?? 'Bab').' — Jejak Sirah')
@section('body-class', 'page-story')

@section('content')
<div class="story"
     data-story
     data-arc="{{ $arc->slug }}"
     data-chapter="{{ $chapter->id }}"
     data-unlock="{{ $preview ? 0 : $chapter->unlock_day_offset }}">

    <div class="story-top">
        <div class="story-progress" data-progress aria-hidden="true"></div>
        <div class="story-bar">
            <a class="story-close" href="{{ $mapUrl }}" aria-label="Kembali ke peta">✕</a>
            <span class="story-chapter">Bab {{ $number }} · {{ $chapter->revision?->title }}</span>
            @if ($preview)<span class="story-preview">Pratonton</span>@endif
            <button type="button" class="story-sound" data-sound aria-pressed="false" aria-label="Bunyi angin">
                <svg viewBox="0 0 24 24"><path d="M4 9h4l5-4v14l-5-4H4z"/><path class="sound-wave" d="M16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11"/></svg>
            </button>
        </div>
    </div>

    {{-- Bab berkunci --}}
    <section class="slide slide-center" data-locked-slide hidden>
        @include('player.partials.scene-bg', ['setting' => 'gurun_malam', 'seed' => 99])
        <div class="slide-inner">
            <p class="eyebrow">Bab {{ $number }}</p>
            <h1>{{ $chapter->revision?->title }}</h1>
            <p class="lead" data-locked-text></p>
            <a class="button button-glow" href="{{ $mapUrl }}">Kembali ke peta</a>
        </div>
    </section>

    {{-- Tajuk --}}
    <section class="slide slide-center slide-title" data-slide>
        @include('player.partials.scene-bg', ['setting' => $firstSetting, 'seed' => 0])
        <div class="slide-inner">
            <p class="eyebrow">Bab {{ $number }} daripada {{ $total }}</p>
            <h1 class="title-reveal">{{ $chapter->revision?->title }}</h1>
            @if ($chapter->revision?->summary)<p class="lead">{{ $chapter->revision->summary }}</p>@endif
            <p class="tap-hint">Sentuh untuk mula</p>
        </div>
    </section>

    {{-- Babak --}}
    @forelse ($scenes as $i => $scene)
        @php $rev = $scene->revision; $label = $rev->label->value; @endphp
        <section class="slide slide-scene scene-{{ $label }}" data-slide data-scene>
            @include('player.partials.scene-bg', ['setting' => $scene->setting?->value ?? 'gurun_malam', 'seed' => $i + 1])
            <div class="slide-inner">
                <div class="scene-meta">
                    <span class="label label-{{ $label }}">{{ $labelNames[$label] }}</span>
                    @if ($preview)
                        <span class="status status-{{ $rev->status->value }}">v{{ $rev->version }} · {{ $rev->status->getLabel() }}</span>
                    @endif
                    @if ($rev->sources->isNotEmpty())
                        <button type="button" class="source-chip" data-open-sheet="sheet-{{ $scene->id }}">Sumber ({{ $rev->sources->count() }})</button>
                    @endif
                </div>
                <div class="scene-text" data-reveal-group>{!! SafeHtml::clean($rev->body_ms) !!}</div>
            </div>

            @if ($rev->sources->isNotEmpty())
                <div class="sheet" id="sheet-{{ $scene->id }}" role="dialog" aria-modal="true" aria-label="Sumber babak" hidden>
                    <div class="sheet-panel">
                        <div class="sheet-head">
                            <h2>Sumber</h2>
                            <button type="button" class="sheet-close" data-close-sheet aria-label="Tutup">✕</button>
                        </div>
                        <ul>
                            @foreach ($rev->sources as $source)
                                <li>
                                    <p class="source-type">{{ $source->type->getLabel() }}</p>
                                    <p class="source-title">{{ $source->title }}@if ($source->reference) — {{ $source->reference }}@endif</p>
                                    @if ($source->narrator || $source->grade)
                                        <p class="muted small">
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
                    </div>
                </div>
            @endif
        </section>

        @foreach ($mediaAfter[$scene->id] ?? [] as $m => $media)
            @include('player.partials.media-slide', ['media' => $media, 'setting' => $scene->setting?->value, 'loopIndex' => $scene->id * 10 + $m])
        @endforeach
    @empty
        <section class="slide slide-center" data-slide>
            @include('player.partials.scene-bg', ['setting' => 'gurun_malam', 'seed' => 5])
            <div class="slide-inner"><p class="lead">Babak untuk bab ini sedang disemak. Datang semula nanti.</p></div>
        </section>
    @endforelse

    {{-- Media tanpa babak tertentu: selepas babak terakhir --}}
    @foreach ($mediaAfter['end'] ?? [] as $m => $media)
        @include('player.partials.media-slide', ['media' => $media, 'setting' => $lastSetting, 'loopIndex' => $m])
    @endforeach

    {{-- Pilihan Yusuf --}}
    @if ($choice)
        <section class="slide slide-center" data-slide data-choice="{{ $chapter->id }}" data-interactive>
            @include('player.partials.scene-bg', ['setting' => $lastSetting, 'seed' => 40])
            <div class="slide-inner panel-glass">
                <p class="eyebrow">Pilihan Yusuf</p>
                <h2>{{ $choice->prompt }}</h2>
                <div class="choice-options">
                    <button type="button" class="choice-btn" data-option="a">{{ $choice->option_a }}</button>
                    <button type="button" class="choice-btn" data-option="b">{{ $choice->option_b }}</button>
                </div>
                <p class="choice-outcome" data-outcome="a" hidden>{{ $choice->outcome_a }}</p>
                <p class="choice-outcome" data-outcome="b" hidden>{{ $choice->outcome_b }}</p>
                <p class="fineprint">Pilihan anda hanya mengubah perjalanan Yusuf. Sejarah sebenar tidak berubah.</p>
            </div>
        </section>
    @endif

    {{-- Fakta atau Mitos? --}}
    @foreach ($quiz as $k => $question)
        <section class="slide slide-center" data-slide data-interactive>
            @include('player.partials.scene-bg', ['setting' => $lastSetting, 'seed' => 50 + $k])
            <div class="slide-inner panel-glass quiz-item" data-answer="{{ $question->answer->value }}">
                <p class="eyebrow">Fakta atau Mitos? @if ($quiz->count() > 1)· {{ $k + 1 }}/{{ $quiz->count() }}@endif</p>
                <p class="quiz-statement">“{{ $question->statement }}”</p>
                <div class="quiz-options">
                    <button type="button" class="choice-btn" data-guess="fakta">Fakta</button>
                    <button type="button" class="choice-btn" data-guess="lemah">Riwayat lemah</button>
                    <button type="button" class="choice-btn" data-guess="mitos">Mitos</button>
                </div>
                <div class="quiz-reveal" hidden>
                    <p class="quiz-verdict" data-verdict></p>
                    <p><strong>Jawapan: {{ $question->answer->getLabel() }}</strong></p>
                    <p>{{ $question->explanation }}</p>
                    @if ($question->source)<p class="fineprint">Rujukan: {{ $question->source->display_name }}</p>@endif
                </div>
            </div>
        </section>
    @endforeach

    {{-- Bintang penutup --}}
    <section class="slide slide-center slide-finish" data-slide data-finish data-interactive>
        @include('player.partials.scene-bg', ['setting' => $lastSetting, 'seed' => 70])
        <div class="slide-inner">
            <button type="button" class="big-lamp" data-complete aria-label="Tandakan jejak bab ini">
                @include('player.partials.star')
            </button>
            <h2 data-finish-title>Sentuh bintang untuk menandakan jejak anda</h2>
            <p class="lead" data-finish-sub>Setiap bab yang selesai menjadi bintang di langit perjalanan anda.</p>

            <div class="after" data-after-complete hidden>
                @if ($sources->isNotEmpty())
                    <p class="eyebrow">Kad Sumber dibuka</p>
                    <div class="cards">
                        @foreach ($sources as $n => $source)
                            <div class="source-card" data-card="{{ $source->id }}" style="--n: {{ $n }}">
                                <span class="source-card-type">{{ $source->type->getLabel() }}</span>
                                <span class="source-card-title">{{ $source->title }}</span>
                                @if ($source->reference)<span class="source-card-ref">{{ $source->reference }}</span>@endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <label for="journal" class="eyebrow journal-label">Jurnal Saksi</label>
                <textarea id="journal" rows="2" data-journal placeholder="Satu pengajaran daripada bab ini…"></textarea>
                <p class="fineprint" data-journal-saved hidden>Disimpan dalam peranti ini.</p>

                <div class="finish-actions">
                    @if ($next)
                        <a class="button button-glow" href="{{ route('player.chapter', ['arc' => $arc->slug, 'chapter' => $next->id] + $q) }}">Bab seterusnya →</a>
                    @endif
                    <a class="button button-ghost" href="{{ $mapUrl }}">Lihat peta</a>
                </div>
            </div>
        </div>
    </section>

    <div class="story-nav">
        <button type="button" class="nav-btn" data-prev aria-label="Sebelumnya">‹</button>
        <button type="button" class="nav-btn nav-next" data-next aria-label="Teruskan">›</button>
    </div>
</div>
@endsection
