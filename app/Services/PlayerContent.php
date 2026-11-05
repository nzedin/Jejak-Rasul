<?php

namespace App\Services;

use App\Enums\RevisionStatus;
use App\Models\Arc;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Kandungan yang dilihat pemain.
 *
 * Mod biasa  : HANYA versi yang diluluskan (published_revision_id) dan item
 *              berstatus diluluskan.
 * Mod pratonton (penulis/penyemak/admin sahaja): versi terkini termasuk draf,
 *              supaya penulis boleh lihat rupa sebenar sebelum semakan.
 */
class PlayerContent
{
    public function __construct(public readonly bool $preview = false) {}

    public static function for(?User $user, bool $wantsPreview): self
    {
        return new self($wantsPreview && $user !== null && $user->isStaff());
    }

    /** @return Collection<int, Arc> */
    public function arcs(): Collection
    {
        return Arc::query()
            ->when(! $this->preview, fn ($q) => $q->where('is_published', true))
            ->orderBy('position')
            ->get()
            ->each(fn (Arc $arc) => $arc->setRelation('visibleChapters', $this->chapters($arc)));
    }

    public function canSeeArc(Arc $arc): bool
    {
        return $this->preview || $arc->is_published;
    }

    /**
     * Bab yang boleh dilihat, setiap satu dengan `revision` (tajuk/ringkasan yang dipapar).
     *
     * @return Collection<int, Chapter>
     */
    public function chapters(Arc $arc): Collection
    {
        return $arc->chapters()
            ->with($this->preview ? 'latestRevision' : 'publishedRevision')
            ->when(! $this->preview, fn ($q) => $q->whereNotNull('published_revision_id'))
            ->get()
            ->each(fn (Chapter $c) => $c->setRelation('revision', $this->revisionOf($c)))
            ->values();
    }

    public function revisionOf(Chapter $chapter)
    {
        return $this->preview ? $chapter->latestRevision : $chapter->publishedRevision;
    }

    /**
     * Babak mengikut susunan, setiap satu dengan `revision` + sumbernya.
     */
    public function scenes(Chapter $chapter): Collection
    {
        $relation = $this->preview ? 'latestRevision' : 'publishedRevision';

        return $chapter->scenes()
            ->with("{$relation}.sources")
            ->when(! $this->preview, fn ($q) => $q->whereNotNull('published_revision_id'))
            ->get()
            ->each(fn ($scene) => $scene->setRelation('revision', $scene->{$relation}))
            ->filter(fn ($scene) => $scene->revision !== null)
            ->values();
    }

    public function choice(Chapter $chapter)
    {
        return $chapter->choices()
            ->when(! $this->preview, fn ($q) => $q->where('status', RevisionStatus::Approved))
            ->latest('id')
            ->first();
    }

    public function quiz(Chapter $chapter): Collection
    {
        return $chapter->quizQuestions()
            ->with('source')
            ->when(! $this->preview, fn ($q) => $q->where('status', RevisionStatus::Approved))
            ->get();
    }
}
