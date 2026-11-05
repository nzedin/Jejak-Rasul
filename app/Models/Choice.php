<?php

namespace App\Models;

use App\Enums\RevisionStatus;
use App\Models\Concerns\HasSimpleReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Choice extends Model
{
    use HasSimpleReview;

    public const REVIEWED_ATTRIBUTES = ['prompt', 'option_a', 'option_b', 'outcome_a', 'outcome_b'];

    /** Item baru sentiasa bermula sebagai draf. */
    protected $attributes = ['status' => 'draf'];

    protected $fillable = ['chapter_id', 'prompt', 'option_a', 'option_b', 'outcome_a', 'outcome_b', 'status', 'edited_by'];

    protected function casts(): array
    {
        return ['status' => RevisionStatus::class];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
