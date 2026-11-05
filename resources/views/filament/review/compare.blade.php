{{--
    Perbandingan versi untuk penyemak.
    $published: revision yang pemain nampak sekarang (boleh null)
    $revision:  revision yang sedang disemak
    $fields:    ['body_ms' => 'Isi babak', ...]
--}}
@php
    use App\Services\TextDiff;
@endphp

<div class="space-y-6 text-sm">
    <p class="text-gray-500 dark:text-gray-400">
        @if ($isLive)
            Ini ialah <strong>versi yang pemain nampak sekarang</strong>.
        @elseif ($published)
            Dibandingkan dengan <strong>versi {{ $published->version }}</strong> (yang pemain nampak sekarang).
        @else
            Kandungan ini <strong>belum pernah diterbitkan</strong> — semua baris adalah baharu.
        @endif
    </p>

    @if ($revision->change_note)
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <span class="font-semibold">Sebab perubahan:</span> {{ $revision->change_note }}
        </div>
    @endif

    @foreach ($fields as $field => $label)
        @php
            $old = $published?->{$field};
            $new = $revision->{$field};
            $old = $old instanceof \BackedEnum ? $old->getLabel() : $old;
            $new = $new instanceof \BackedEnum ? $new->getLabel() : $new;
        @endphp
        <div>
            <h3 class="mb-2 font-semibold">{{ $label }}</h3>
            <div class="space-y-1 rounded-lg border border-gray-200 p-3 dark:border-white/10">
                @forelse (TextDiff::lines($old, $new) as $line)
                    <div @class([
                        'rounded px-2 py-1',
                        'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $line['type'] === 'added',
                        'bg-danger-50 text-danger-700 line-through dark:bg-danger-500/10 dark:text-danger-400' => $line['type'] === 'removed',
                    ])>
                        <span class="select-none font-mono">{{ ['added' => '+', 'removed' => '−', 'same' => ' '][$line['type']] }}</span>
                        {{ $line['text'] }}
                    </div>
                @empty
                    <div class="text-gray-400">(kosong)</div>
                @endforelse
            </div>
        </div>
    @endforeach

    @if (method_exists($revision, 'sources'))
        <div>
            <h3 class="mb-2 font-semibold">Sumber dipautkan</h3>
            @php $sources = $revision->sources; @endphp
            @if ($sources->isEmpty())
                <p class="text-danger-600">Tiada sumber dipautkan.</p>
            @else
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($sources as $source)
                        <li>
                            <span class="font-medium">{{ $source->display_name }}</span>
                            @if ($source->grade) <span class="text-gray-500">({{ $source->grade }})</span> @endif
                            @if ($source->narrator) — perawi: {{ $source->narrator }} @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if ($revision->reviews->isNotEmpty())
        <div>
            <h3 class="mb-2 font-semibold">Sejarah semakan</h3>
            <ul class="space-y-1">
                @foreach ($revision->reviews as $review)
                    <li>
                        <span @class(['font-medium', 'text-success-600' => $review->decision === 'lulus', 'text-danger-600' => $review->decision !== 'lulus'])>
                            {{ $review->decision === 'lulus' ? 'Lulus' : 'Minta pindaan' }}
                        </span>
                        oleh {{ $review->reviewer?->name }} · {{ $review->created_at?->diffForHumans() }}
                        @if ($review->comment) — “{{ $review->comment }}” @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
