<?php

namespace App\Models;

use App\Contracts\Revision;
use App\Enums\RevisionStatus;
use App\Enums\SceneLabel;
use App\Models\Concerns\IsRevision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SceneRevision extends Model implements Revision
{
    use IsRevision;

    public const CONTENT_ATTRIBUTES = ['body_ms', 'label'];

    protected $fillable = [
        'scene_id', 'version', 'body_ms', 'label', 'status',
        'change_note', 'created_by', 'edited_by', 'submitted_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RevisionStatus::class,
            'label' => SceneLabel::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class)->withPivot('note');
    }

    public function revisable(): Model
    {
        return $this->scene;
    }
}
