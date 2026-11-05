<?php

namespace App\Filament\Resources\SceneRevisions;

use App\Enums\RevisionStatus;
use App\Filament\Actions\WorkflowActions;
use App\Filament\Resources\SceneRevisions\Pages\ListSceneRevisions;
use App\Models\SceneRevision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

/** Barisan semakan babak + sejarah semua versi babak. */
class SceneRevisionResource extends Resource
{
    protected static ?string $model = SceneRevision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Semakan';

    protected static ?string $navigationLabel = 'Semakan babak';

    protected static ?string $modelLabel = 'versi babak';

    protected static ?string $pluralModelLabel = 'Semakan babak';

    public static function getNavigationBadge(): ?string
    {
        $count = SceneRevision::where('status', RevisionStatus::Submitted)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['scene.chapter.latestRevision', 'scene.publishedRevision', 'author', 'editor', 'sources', 'reviews.reviewer']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('scene.chapter.latestRevision.title')->label('Bab')->wrap(),
                TextColumn::make('scene.position')->label('Babak #'),
                TextColumn::make('version')->label('Versi')->prefix('v'),
                TextColumn::make('label')->label('Label')->badge(),
                TextColumn::make('excerpt')->label('Isi')
                    ->state(fn (SceneRevision $record) => Str::limit(strip_tags($record->body_ms), 100))
                    ->wrap(),
                TextColumn::make('sources_count')->counts('sources')->label('Sumber'),
                TextColumn::make('status')->label('Status')->badge(),
                IconColumn::make('is_live')->label('Diterbitkan')->boolean()
                    ->state(fn (SceneRevision $record) => $record->isPublished()),
                TextColumn::make('editor.name')->label('Penyunting'),
                TextColumn::make('submitted_at')->label('Dihantar')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')
                    ->options(RevisionStatus::class)
                    ->default(RevisionStatus::Submitted->value),
            ])
            ->recordActions([
                WorkflowActions::compare(['body_ms' => 'Isi babak', 'label' => 'Label']),
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
            'index' => ListSceneRevisions::route('/'),
        ];
    }
}
