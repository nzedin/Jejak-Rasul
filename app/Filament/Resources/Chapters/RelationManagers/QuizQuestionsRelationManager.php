<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Enums\QuizAnswer;
use App\Filament\Actions\WorkflowActions;
use App\Models\Source;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * "Fakta atau Mitos?" — kuiz yang melatih pemain menapis kisah sirah.
 */
class QuizQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'quizQuestions';

    protected static ?string $title = 'Fakta atau Mitos?';

    protected static ?string $modelLabel = 'soalan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('statement')->label('Kenyataan / kisah')->required()->rows(3)->columnSpanFull(),
            Select::make('answer')->label('Jawapan')->options(QuizAnswer::class)->required(),
            Select::make('source_id')->label('Sumber rujukan')
                ->options(fn () => Source::orderBy('title')->get()->pluck('display_name', 'id'))
                ->searchable(),
            Textarea::make('explanation')->label('Penerangan (dipapar selepas pemain menjawab)')
                ->required()->rows(4)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                TextColumn::make('statement')->label('Kenyataan')->limit(80)->wrap(),
                TextColumn::make('answer')->label('Jawapan')->badge(),
                TextColumn::make('source.title')->label('Sumber'),
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
