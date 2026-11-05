<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Filament\Actions\WorkflowActions;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Pilihan Yusuf (watak rekaan). Pilihan hanya mengubah perjalanan Yusuf —
 * sejarah sebenar tidak berubah. Jika diubah selepas diluluskan, ia kembali
 * menjadi draf secara automatik.
 */
class ChoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'choices';

    protected static ?string $title = 'Pilihan Yusuf';

    protected static ?string $modelLabel = 'pilihan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('prompt')->label('Soalan kepada pemain')->required()->columnSpanFull()
                ->placeholder('cth. Yusuf terdengar khabar rombongan bertolak malam ini. Apa dia buat?'),
            Section::make('Pilihan A')->schema([
                TextInput::make('option_a')->label('Pilihan')->required(),
                Textarea::make('outcome_a')->label('Kesan kepada perjalanan Yusuf')->required()->rows(3),
            ]),
            Section::make('Pilihan B')->schema([
                TextInput::make('option_b')->label('Pilihan')->required(),
                Textarea::make('outcome_b')->label('Kesan kepada perjalanan Yusuf')->required()->rows(3),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prompt')->label('Soalan')->wrap(),
                TextColumn::make('option_a')->label('A'),
                TextColumn::make('option_b')->label('B'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->mutateFormDataUsing(fn (array $data) => [...$data, 'edited_by' => auth()->id()]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->mutateFormDataUsing(fn (array $data) => [...$data, 'edited_by' => auth()->id()]),
                ActionGroup::make([
                    ...WorkflowActions::all(),
                    DeleteAction::make()->visible(fn () => auth()->user()->canWrite()),
                ]),
            ]);
    }
}
