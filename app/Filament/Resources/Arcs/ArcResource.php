<?php

namespace App\Filament\Resources\Arcs;

use App\Filament\Resources\Arcs\Pages\CreateArc;
use App\Filament\Resources\Arcs\Pages\EditArc;
use App\Filament\Resources\Arcs\Pages\ListArcs;
use App\Models\Arc;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class ArcResource extends Resource
{
    protected static ?string $model = Arc::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Kandungan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'arka';

    protected static ?string $pluralModelLabel = 'Arka cerita';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Tajuk arka')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, ?Arc $record) => $record ? null : $set('slug', Str::slug($state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Textarea::make('description')->label('Penerangan')->rows(3)->columnSpanFull(),
            TextInput::make('position')->label('Susunan')->numeric()->default(0),
            Toggle::make('is_published')->label('Papar kepada pemain')
                ->helperText('Hanya bab & babak yang diluluskan akan dipaparkan.')
                ->disabled(fn () => ! auth()->user()->canReview()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')->label('#'),
                TextColumn::make('title')->label('Arka')->searchable(),
                TextColumn::make('chapters_count')->counts('chapters')->label('Bab'),
                IconColumn::make('is_published')->label('Dipapar')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->canWrite();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === \App\Enums\Role::Admin;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArcs::route('/'),
            'create' => CreateArc::route('/create'),
            'edit' => EditArc::route('/{record}/edit'),
        ];
    }
}
