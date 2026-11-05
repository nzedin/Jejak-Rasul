<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Satu versi kandungan (tajuk bab, isi babak) yang perlu disemak
 * sebelum diterbitkan kepada pemain.
 *
 * @property int $version
 * @property \App\Enums\RevisionStatus $status
 */
interface Revision
{
    /** Rekod induk (Chapter / Scene) yang memegang published_revision_id. */
    public function revisable(): Model;

    public function author(): BelongsTo;

    public function reviews(): MorphMany;

    /** Medan kandungan yang disalin apabila revision baru dicipta. */
    public function contentAttributes(): array;

    public function isAuthoredBy(User $user): bool;
}
