<?php

namespace App\Models\Concerns;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait IsRevision
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->latest();
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /** Pencipta ATAU penyunting terakhir — kedua-duanya tidak boleh menyemak versi ini. */
    public function isAuthoredBy(User $user): bool
    {
        return in_array((int) $user->id, [(int) $this->created_by, (int) $this->edited_by], true);
    }

    public function isPublished(): bool
    {
        return (int) $this->revisable()->published_revision_id === (int) $this->id;
    }

    public function contentAttributes(): array
    {
        return $this->only(static::CONTENT_ATTRIBUTES);
    }
}
