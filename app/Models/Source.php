<?php

namespace App\Models;

use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Source extends Model
{
    protected $fillable = [
        'type', 'title', 'reference', 'narrator', 'grade',
        'text_ar', 'text_ms', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['type' => SourceType::class];
    }

    public function sceneRevisions(): BelongsToMany
    {
        return $this->belongsToMany(SceneRevision::class)->withPivot('note');
    }

    /** Label ringkas untuk pilihan dropdown. */
    public function getDisplayNameAttribute(): string
    {
        return $this->reference ? "{$this->title} — {$this->reference}" : $this->title;
    }
}
