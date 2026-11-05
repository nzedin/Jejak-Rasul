<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Untuk Chapter & Scene. Pemain sentiasa membaca publishedRevision;
 * penulis sentiasa mengedit latestRevision.
 */
trait HasRevisions
{
    abstract protected static function revisionModel(): string;

    public function revisions(): HasMany
    {
        return $this->hasMany(static::revisionModel())->orderByDesc('version');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(static::revisionModel())->ofMany('version', 'max');
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(static::revisionModel(), 'published_revision_id');
    }

    public function isPublished(): bool
    {
        return $this->published_revision_id !== null;
    }

    /** Ada perubahan yang belum diluluskan? */
    public function hasPendingChanges(): bool
    {
        $latest = $this->latestRevision;

        return $latest !== null && (int) $latest->id !== (int) $this->published_revision_id;
    }
}
