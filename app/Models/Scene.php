<?php

namespace App\Models;

use App\Enums\SceneSetting;
use App\Models\Concerns\HasRevisions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scene extends Model
{
    use HasRevisions;

    protected $fillable = ['chapter_id', 'position', 'setting'];

    protected function casts(): array
    {
        return ['setting' => SceneSetting::class];
    }

    protected static function revisionModel(): string
    {
        return SceneRevision::class;
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
