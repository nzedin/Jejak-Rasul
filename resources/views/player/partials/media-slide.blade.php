{{-- Satu skrin media dalam mod cerita. $media: App\Models\ChapterMedia --}}
@php
    $type = $media->type->value;
    $embed = $media->embedUrl();
    $file = $media->fileUrl();
@endphp
<section class="slide slide-center slide-media" data-slide data-interactive data-media>
    @include('player.partials.scene-bg', ['setting' => $setting ?? 'gurun_malam', 'seed' => 80 + $loopIndex])
    <div class="slide-inner panel-glass media-panel">
        <p class="eyebrow">
            {{ $media->type->getLabel() }}
            @if ($preview)<span class="status status-{{ $media->status->value }}">{{ $media->status->getLabel() }}</span>@endif
        </p>

        @if ($type === 'gambar' && $file)
            <figure class="media-frame media-image">
                <img src="{{ $file }}" alt="{{ $media->title }}" loading="lazy" decoding="async">
            </figure>
        @elseif ($type === 'video' && $embed)
            <div class="media-frame media-video">
                <iframe src="{{ $embed }}" title="{{ $media->title }}" loading="lazy"
                        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
        @elseif ($type === 'video' && $file)
            <div class="media-frame media-video">
                <video src="{{ $file }}" controls playsinline preload="metadata"></video>
            </div>
        @endif

        <h2 class="media-title">{{ $media->title }}</h2>
        @if ($media->caption)<p class="media-caption">{{ $media->caption }}</p>@endif

        @if ($type === 'pautan' && $media->safeUrl())
            <a class="link-card" href="{{ $media->safeUrl() }}" target="_blank" rel="noopener noreferrer nofollow">
                <span class="link-card-host">{{ $media->linkHost() }}</span>
                <span class="link-card-cta">Buka pautan ↗</span>
            </a>
        @endif

        @if ($media->credit || $media->source)
            <p class="fineprint">
                @if ($media->credit){{ $media->credit }}@endif
                @if ($media->credit && $media->source) · @endif
                @if ($media->source)Rujukan: {{ $media->source->display_name }}@endif
            </p>
        @endif
    </div>
</section>
