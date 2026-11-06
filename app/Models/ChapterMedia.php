<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Enums\RevisionStatus;
use App\Models\Concerns\HasSimpleReview;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChapterMedia extends Model
{
    use HasSimpleReview;

    /** Mengubah mana-mana medan ini selepas diluluskan → kembali draf. */
    public const REVIEWED_ATTRIBUTES = ['type', 'title', 'caption', 'file_path', 'url', 'credit', 'source_id'];

    protected $table = 'chapter_media';

    protected $attributes = ['status' => 'draf'];

    protected $fillable = [
        'chapter_id', 'after_scene_id', 'type', 'title', 'caption', 'file_path', 'url',
        'credit', 'source_id', 'depiction_confirmed', 'position', 'status', 'edited_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'status' => RevisionStatus::class,
            'depiction_confirmed' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Buang fail lama apabila diganti atau dipadam.
        static::updated(function (self $media) {
            $old = $media->getOriginal('file_path');
            if ($media->wasChanged('file_path') && $old) {
                Storage::disk('public')->delete($old);
            }
        });
        static::deleted(function (self $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
        });
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function afterScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'after_scene_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /** URL fail yang dimuat naik (relatif kepada hos semasa, perlukan `php artisan storage:link`). */
    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/'.ltrim($this->file_path, '/')) : null;
    }

    public function embedUrl(): ?string
    {
        return $this->type === MediaType::Video ? VideoEmbed::from($this->url) : null;
    }

    /** Hanya pautan http/https dibenarkan dipapar sebagai pautan. */
    public function safeUrl(): ?string
    {
        return $this->url && in_array(parse_url($this->url, PHP_URL_SCHEME), ['http', 'https'], true) ? $this->url : null;
    }

    public function linkHost(): ?string
    {
        return $this->url ? parse_url($this->url, PHP_URL_HOST) : null;
    }
}
