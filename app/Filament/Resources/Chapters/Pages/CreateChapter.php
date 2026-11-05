<?php

namespace App\Filament\Resources\Chapters\Pages;

use App\Filament\Resources\Chapters\ChapterResource;
use App\Models\Arc;
use App\Services\ChapterEditor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ChapterEditor::class)->create(
            Arc::findOrFail($data['arc_id']),
            $data,
            auth()->user(),
            $data['change_note'] ?? null,
        );
    }
}
