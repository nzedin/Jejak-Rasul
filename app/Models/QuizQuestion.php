<?php

namespace App\Models;

use App\Enums\QuizAnswer;
use App\Enums\RevisionStatus;
use App\Models\Concerns\HasSimpleReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasSimpleReview;

    public const REVIEWED_ATTRIBUTES = ['statement', 'answer', 'explanation', 'source_id'];

    /** Item baru sentiasa bermula sebagai draf. */
    protected $attributes = ['status' => 'draf'];

    protected $fillable = ['chapter_id', 'statement', 'answer', 'explanation', 'source_id', 'position', 'status', 'edited_by'];

    protected function casts(): array
    {
        return [
            'answer' => QuizAnswer::class,
            'status' => RevisionStatus::class,
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
