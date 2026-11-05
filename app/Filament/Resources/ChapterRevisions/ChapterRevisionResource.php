<?php

namespace App\Filament\Resources\ChapterRevisions;

use App\Enums\RevisionStatus;
use App\Filament\Actions\WorkflowActions;
use App\Filament\Resources\ChapterRevisions\Pages\ListChapterRevisions;
use App\Models\ChapterRevision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Barisan semakan tajuk/ringkasan bab. */
class ChapterRevisionResource extends Resource
{
    protected static ?string $model = ChapterRevision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Semakan';

    protected static ?string $navigationLabel = 'Semakan bab';

    protected static ?string $modelLabel = 'versi bab';

    protected static ?string $pluralModelLabel = 'Semakan bab';

    public static function getNavigationBadge(): ?string
    {
        $count = ChapterRevision::where('status', RevisionStatus::Submitted)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['chapter.arc', 'chapter.publishedRevision', 'editor', 'reviews.reviewer']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('chapter.arc.title')->label('Arka'),
                TextColumn::make('chapter.position')->label('Bab #'),
                TextColumn::make('version')->label('Versi')->prefix('v'),
                TextColumn::make('title')->label('Tajuk')->wrap(),
                TextColumn::make('change_note')->label('Sebab perubahan')->wrap(),
                TextColumn::make('status')->label('Status')->badge(),
                IconColumn::make('is_live')->label('Diterbitkan')->boolean()
                    ->state(fn (ChapterRevision $record) => $record->isPublished()),
                TextColumn::make('editor.name')->label('Penyunting'),
                TextColumn::make('submitted_at')->label('Dihantar')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')
                    ->options(RevisionStatus::class)
                    ->default(RevisionStatus::Submitted->value),
            ])
            ->recordActions([
                WorkflowActions::compare(['title' => 'Tajuk', 'summary' => 'Ringkasan']),
                WorkflowActions::approve(),
                WorkflowActions::requestChanges(),
                WorkflowActions::restore(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChapterRevisions::route('/'),
        ];
    }
}
