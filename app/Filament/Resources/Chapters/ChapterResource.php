<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Actions\WorkflowActions;
use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Filament\Resources\Chapters\RelationManagers\ChapterRevisionsRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\ChoicesRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\QuizQuestionsRelationManager;
use App\Filament\Resources\Chapters\RelationManagers\ScenesRelationManager;
use App\Models\Chapter;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Kandungan';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'bab';

    protected static ?string $pluralModelLabel = 'Bab';

    public static function getRecordTitle(?Model $record): string
    {
        return $record?->display_title ?? 'Bab';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['arc', 'latestRevision', 'publishedRevision']);
    }

    /**
     * Tajuk & ringkasan disimpan sebagai REVISION, bukan terus pada bab.
     * Lihat Pages\CreateChapter & Pages\EditChapter.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Bab')->columns(2)->schema([
                Select::make('arc_id')->label('Arka')
                    ->relationship('arc', 'title')
                    ->required()
                    ->disabledOn('edit'),
                TextInput::make('unlock_day_offset')->label('Dibuka pada hari ke-')
                    ->numeric()->minValue(0)->default(0)
                    ->helperText('0 = terus dibuka. 1 = sehari selepas pemain mula, dan seterusnya.'),
                TextInput::make('title')->label('Tajuk bab')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('summary')->label('Ringkasan')->rows(3)->columnSpanFull(),
            ]),
            Section::make('Catatan perubahan')->schema([
                TextInput::make('change_note')->label('Sebab perubahan')
                    ->placeholder('cth. Tajuk dibetulkan ikut riwayat Sahih al-Bukhari')
                    ->required(fn (string $operation) => $operation === 'edit')
                    ->helperText('Penyemak akan baca catatan ini semasa menyemak.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')->label('#'),
                TextColumn::make('arc.title')->label('Arka'),
                TextColumn::make('latestRevision.title')->label('Tajuk (versi terkini)')->wrap()->searchable(),
                TextColumn::make('latestRevision.status')->label('Status')->badge(),
                TextColumn::make('latestRevision.version')->label('Versi')->prefix('v'),
                IconColumn::make('is_live')->label('Diterbitkan')->boolean()
                    ->state(fn (Chapter $record) => $record->isPublished()),
                TextColumn::make('scenes_count')->counts('scenes')->label('Babak'),
            ])
            ->filters([
                SelectFilter::make('arc_id')->label('Arka')->relationship('arc', 'title'),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make(WorkflowActions::all(fn (Chapter $c) => $c->latestRevision)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ScenesRelationManager::class,
            ChoicesRelationManager::class,
            QuizQuestionsRelationManager::class,
            ChapterRevisionsRelationManager::class,
        ];
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->canWrite();
    }

    public static function canDelete(Model $record): bool
    {
        // Bab yang pernah diterbitkan tidak dipadam — elak kehilangan sejarah semakan.
        return (bool) auth()->user()?->canWrite() && ! $record->isPublished();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChapters::route('/'),
            'create' => CreateChapter::route('/create'),
            'edit' => EditChapter::route('/{record}/edit'),
        ];
    }
}
