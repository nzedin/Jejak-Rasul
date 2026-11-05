<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Enums\SceneLabel;
use App\Exceptions\WorkflowException;
use App\Filament\Actions\WorkflowActions;
use App\Models\Scene;
use App\Models\Source;
use App\Services\SceneEditor;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Babak dalam satu bab. Seret untuk ubah susunan jalan cerita.
 * Isi, label & sumber disimpan sebagai revision yang perlu disemak.
 */
class ScenesRelationManager extends RelationManager
{
    protected static string $relationship = 'scenes';

    protected static ?string $title = 'Babak (jalan cerita)';

    protected static ?string $modelLabel = 'babak';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('label')->label('Label kesahihan')
                ->options(SceneLabel::class)
                ->required()
                ->helperText('⚪ Rekaan hanya untuk dialog watak rekaan & gambaran suasana — bukan fakta baru.'),
            RichEditor::make('body_ms')->label('Isi babak')
                ->required()
                ->toolbarButtons(['bold', 'italic', 'blockquote', 'bulletList', 'orderedList', 'undo', 'redo'])
                ->columnSpanFull(),
            Select::make('sources')->label('Sumber rujukan')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => Source::orderBy('title')->get()->pluck('display_name', 'id'))
                ->required(fn (callable $get) => in_array($get('label'), [SceneLabel::Sahih, SceneLabel::Riwayat, 'sahih', 'riwayat'], true))
                ->helperText('Wajib untuk babak bertanda 🟢 Sahih atau 🟡 Riwayat.')
                ->columnSpanFull(),
            TextInput::make('change_note')->label('Sebab perubahan')
                ->placeholder('cth. Ditulis semula ikut riwayat Sahih al-Bukhari')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['latestRevision', 'publishedRevision']))
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')->label('#'),
                TextColumn::make('latestRevision.label')->label('Label')->badge(),
                TextColumn::make('excerpt')->label('Isi (versi terkini)')
                    ->state(fn (Scene $record) => Str::limit(strip_tags((string) $record->latestRevision?->body_ms), 120))
                    ->wrap(),
                TextColumn::make('latestRevision.status')->label('Status')->badge(),
                TextColumn::make('latestRevision.version')->label('Versi')->prefix('v'),
                IconColumn::make('is_live')->label('Diterbitkan')->boolean()
                    ->state(fn (Scene $record) => $record->isPublished()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->using(fn (array $data) => $this->guard(fn () => app(SceneEditor::class)->create(
                        $this->getOwnerRecord(),
                        $data,
                        $data['sources'] ?? [],
                        auth()->user(),
                        $data['change_note'] ?? null,
                    ))),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->fillForm(fn (Scene $record) => [
                        'label' => $record->latestRevision?->label?->value,
                        'body_ms' => $record->latestRevision?->body_ms,
                        'sources' => $record->latestRevision?->sources()->pluck('sources.id')->all() ?? [],
                        'change_note' => null,
                    ])
                    ->using(fn (Scene $record, array $data) => $this->guard(fn () => app(SceneEditor::class)->edit(
                        $record,
                        $data,
                        $data['sources'] ?? [],
                        auth()->user(),
                        $data['change_note'] ?? null,
                    )))
                    ->successNotificationTitle('Draf babak disimpan. Hantar untuk semakan.'),
                ActionGroup::make([
                    WorkflowActions::compare(['body_ms' => 'Isi babak', 'label' => 'Label'], fn (Scene $s) => $s->latestRevision),
                    ...WorkflowActions::all(fn (Scene $s) => $s->latestRevision),
                    DeleteAction::make()
                        ->visible(fn (Scene $record) => auth()->user()->canWrite() && ! $record->isPublished()),
                ]),
            ]);
    }

    /** Tukar WorkflowException kepada notifikasi dan bukannya ralat 500. */
    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (WorkflowException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            throw new Halt;
        }
    }
}
