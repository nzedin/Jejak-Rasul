<?php

namespace App\Models;

use App\Models\Concerns\HasRevisions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasRevisions;

    protected $fillable = ['arc_id', 'position', 'unlock_day_offset'];

    protected static function revisionModel(): string
    {
        return ChapterRevision::class;
    }

    public function arc(): BelongsTo
    {
        return $this->belongsTo(Arc::class);
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('position');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ChapterMedia::class)->orderBy('position');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    /** Hanya bab yang ada versi diluluskan — untuk paparan pemain. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_revision_id');
    }

    /** Tajuk untuk panel: versi terkini (termasuk draf). */
    public function getDisplayTitleAttribute(): string
    {
        return $this->latestRevision?->title ?? "Bab #{$this->id}";
    }
}
