<?php

namespace App\Services;

use App\Enums\SceneSetting;
use App\Exceptions\WorkflowException;
use App\Models\Chapter;
use App\Models\Scene;
use App\Models\SceneRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SceneEditor
{
    public function __construct(private RevisionWriter $writer) {}

    /**
     * @param  array{body_ms: string, label: string|\App\Enums\SceneLabel, position?: int}  $data
     * @param  array<int>  $sourceIds
     */
    public function create(Chapter $chapter, array $data, array $sourceIds, User $author, ?string $changeNote = null): Scene
    {
        $this->ensureWriter($author);

        return DB::transaction(function () use ($chapter, $data, $sourceIds, $author, $changeNote) {
            $scene = $chapter->scenes()->create([
                'position' => $data['position'] ?? ((int) $chapter->scenes()->max('position') + 1),
                'setting' => $data['setting'] ?? SceneSetting::DesertNight,
            ]);

            $this->write($scene, $data, $sourceIds, $author, $changeNote ?? 'Babak baru');

            return $scene->fresh();
        });
    }

    /**
     * Tulis semula jalan cerita / tukar label / tukar sumber babak.
     */
    public function edit(Scene $scene, array $data, array $sourceIds, User $author, ?string $changeNote = null): SceneRevision
    {
        $this->ensureWriter($author);

        return DB::transaction(function () use ($scene, $data, $sourceIds, $author, $changeNote) {
            // Latar ialah hiasan visual — terus disimpan tanpa semakan.
            if (isset($data['setting'])) {
                $scene->update(['setting' => $data['setting']]);
            }

            return $this->write($scene, $data, $sourceIds, $author, $changeNote);
        });
    }

    private function write(Scene $scene, array $data, array $sourceIds, User $author, ?string $changeNote): SceneRevision
    {
        /** @var SceneRevision $revision */
        $revision = $this->writer->write($scene, [
            'body_ms' => $data['body_ms'],
            'label' => $data['label'],
        ], $author, $changeNote);

        $revision->sources()->sync($sourceIds);

        return $revision;
    }

    private function ensureWriter(User $user): void
    {
        if (! $user->canWrite()) {
            throw new WorkflowException('Hanya penulis atau admin boleh mengubah kandungan.');
        }
    }
}
