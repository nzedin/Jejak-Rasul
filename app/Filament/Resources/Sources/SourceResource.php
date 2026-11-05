<?php

namespace App\Filament\Resources\Sources;

use App\Enums\SourceType;
use App\Filament\Resources\Sources\Pages\CreateSource;
use App\Filament\Resources\Sources\Pages\EditSource;
use App\Filament\Resources\Sources\Pages\ListSources;
use App\Models\Source;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static string|UnitEnum|null $navigationGroup = 'Rujukan';

    protected static ?string $modelLabel = 'sumber';

    protected static ?string $pluralModelLabel = 'Sumber rujukan';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rujukan')->columns(2)->schema([
                Select::make('type')->label('Jenis')->options(SourceType::class)->required(),
                TextInput::make('title')->label('Kitab / sumber')->required()
                    ->placeholder('cth. Sahih al-Bukhari'),
                TextInput::make('reference')->label('Rujukan')
                    ->placeholder('cth. Kitab, bab & nombor hadis'),
                TextInput::make('narrator')->label('Perawi'),
                TextInput::make('grade')->label('Darjat')->placeholder('sahih / hasan / da\'if'),
            ]),
            Section::make('Teks')->schema([
                Textarea::make('text_ar')->label('Teks Arab')->rows(4)->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('text_ms')->label('Terjemahan / maksud')->rows(4),
                Textarea::make('notes')->label('Nota penyemak')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('title')->label('Sumber')->searchable(),
                TextColumn::make('reference')->label('Rujukan')->searchable()->wrap(),
                TextColumn::make('grade')->label('Darjat'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(SourceType::class),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canWrite() || auth()->user()?->canReview();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSources::route('/'),
            'create' => CreateSource::route('/create'),
            'edit' => EditSource::route('/{record}/edit'),
        ];
    }
}
