<?php

namespace App\Models;

use App\Contracts\Revision;
use App\Enums\RevisionStatus;
use App\Models\Concerns\IsRevision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterRevision extends Model implements Revision
{
    use IsRevision;

    public const CONTENT_ATTRIBUTES = ['title', 'summary'];

    protected $fillable = [
        'chapter_id', 'version', 'title', 'summary', 'status',
        'change_note', 'created_by', 'edited_by', 'submitted_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RevisionStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function revisable(): Model
    {
        return $this->chapter;
    }
}
