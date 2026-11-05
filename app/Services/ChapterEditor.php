<?php

namespace App\Services;

use App\Exceptions\WorkflowException;
use App\Models\Arc;
use App\Models\Chapter;
use App\Models\ChapterRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChapterEditor
{
    public function __construct(private RevisionWriter $writer) {}

    /**
     * @param  array{title: string, summary?: ?string, unlock_day_offset?: int, position?: int}  $data
     */
    public function create(Arc $arc, array $data, User $author, ?string $changeNote = null): Chapter
    {
        $this->ensureWriter($author);

        return DB::transaction(function () use ($arc, $data, $author, $changeNote) {
            $chapter = $arc->chapters()->create([
                'position' => $data['position'] ?? ((int) $arc->chapters()->max('position') + 1),
                'unlock_day_offset' => $data['unlock_day_offset'] ?? 0,
            ]);

            $this->writer->write($chapter, $this->content($data), $author, $changeNote ?? 'Bab baru');

            return $chapter->fresh();
        });
    }

    /**
     * Ubah tajuk / ringkasan bab. Pemain terus nampak versi lama sehingga
     * versi baru diluluskan.
     */
    public function edit(Chapter $chapter, array $data, User $author, ?string $changeNote = null): ChapterRevision
    {
        $this->ensureWriter($author);

        return DB::transaction(function () use ($chapter, $data, $author, $changeNote) {
            // Medan struktur (bukan kandungan) boleh terus disimpan.
            $chapter->fill(array_intersect_key($data, array_flip(['unlock_day_offset'])))->save();

            return $this->writer->write($chapter, $this->content($data), $author, $changeNote);
        });
    }

    private function content(array $data): array
    {
        return [
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
        ];
    }

    private function ensureWriter(User $user): void
    {
        if (! $user->canWrite()) {
            throw new WorkflowException('Hanya penulis atau admin boleh mengubah kandungan.');
        }
    }
}
