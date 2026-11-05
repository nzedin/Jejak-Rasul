<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Filament\Actions\WorkflowActions;
use App\Models\ChapterRevision;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Sejarah semua versi tajuk/ringkasan bab. Baca sahaja. */
class ChapterRevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Sejarah versi bab';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('version', 'desc')
            ->columns([
                TextColumn::make('version')->label('Versi')->prefix('v'),
                TextColumn::make('title')->label('Tajuk')->wrap(),
                TextColumn::make('status')->label('Status')->badge(),
                IconColumn::make('is_live')->label('Diterbitkan')->boolean()
                    ->state(fn (ChapterRevision $record) => $record->isPublished()),
                TextColumn::make('change_note')->label('Sebab perubahan')->wrap(),
                TextColumn::make('editor.name')->label('Penyunting'),
                TextColumn::make('updated_at')->label('Dikemas kini')->since(),
            ])
            ->recordActions([
                WorkflowActions::compare(['title' => 'Tajuk', 'summary' => 'Ringkasan']),
                WorkflowActions::restore(),
            ]);
    }
}
