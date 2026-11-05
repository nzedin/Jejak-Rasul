<?php

namespace App\Models\Concerns;

use App\Enums\RevisionStatus;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Untuk item kecil (pilihan Yusuf, soalan kuiz) yang tidak perlu sejarah versi
 * penuh: jika item yang sudah diluluskan diubah, ia kembali menjadi draf dan
 * tersembunyi daripada pemain sehingga disemak semula.
 */
trait HasSimpleReview
{
    public static function bootHasSimpleReview(): void
    {
        static::saving(function (self $item) {
            if ($item->exists
                && $item->getOriginal('status') === RevisionStatus::Approved
                && $item->isDirty(static::REVIEWED_ATTRIBUTES)) {
                $item->status = RevisionStatus::Draft;
            }
        });
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->latest();
    }

    public function isAuthoredBy(User $user): bool
    {
        return (int) $this->edited_by === (int) $user->id;
    }
}
