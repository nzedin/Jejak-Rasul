<?php

namespace App\Services;

use App\Enums\RevisionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Peraturan menulis revision (dikongsi oleh ChapterEditor & SceneEditor):
 *
 * - Versi terkini masih draf / perlu pindaan → kemas kini versi yang sama.
 * - Versi terkini sedang dihantar untuk semakan → tarik balik ke draf dan kemas kini.
 * - Versi terkini sudah diluluskan → cipta versi BARU (draf). Versi yang
 *   diterbitkan tidak disentuh, jadi pemain terus nampak versi lama.
 */
class RevisionWriter
{
    /**
     * @param  Model&\App\Models\Concerns\HasRevisions  $parent
     */
    public function write(Model $parent, array $content, User $author, ?string $changeNote = null): Model
    {
        $latest = $parent->revisions()->first(); // orderByDesc('version')

        if ($latest && ($latest->status->isEditable() || $latest->status === RevisionStatus::Submitted)) {
            $latest->fill($content);
            $latest->status = RevisionStatus::Draft;
            $latest->submitted_at = null;
            $latest->change_note = $changeNote ?? $latest->change_note;
            $latest->edited_by = $author->id;
            $latest->save();

            return $latest;
        }

        $revision = $parent->revisions()->create([
            ...$content,
            'version' => ($latest?->version ?? 0) + 1,
            'status' => RevisionStatus::Draft,
            'change_note' => $changeNote,
            'created_by' => $author->id,
            'edited_by' => $author->id,
        ]);

        $parent->unsetRelation('latestRevision');

        return $revision;
    }
}
