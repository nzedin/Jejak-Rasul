<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Enums\MediaType;
use App\Filament\Actions\WorkflowActions;
use App\Models\ChapterMedia;
use App\Models\Source;
use App\Support\VideoEmbed;
use Closure;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Gambar, video & pautan yang dibenamkan dalam mod cerita.
 * Melalui semakan seperti kandungan lain.
 */
class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Media (gambar, video, pautan)';

    protected static ?string $modelLabel = 'media';

    private static function typeIs(callable $get, MediaType ...$types): bool
    {
        $value = $get('type');
        $value = $value instanceof MediaType ? $value : MediaType::tryFrom((string) $value);

        return in_array($value, $types, true);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')->label('Jenis')
                ->options(MediaType::class)
                ->default(MediaType::Image->value)
                ->required()
                ->live(),
            TextInput::make('title')->label('Tajuk')->required()->maxLength(255),

            FileUpload::make('file_path')->label(fn (callable $get) => self::typeIs($get, MediaType::Video) ? 'Muat naik video (MP4/WebM, maks. 12MB)' : 'Muat naik gambar')
                ->disk('public')
                ->directory('bab-media')
                ->visibility('public')
                ->acceptedFileTypes(fn (callable $get) => self::typeIs($get, MediaType::Video)
                    ? ['video/mp4', 'video/webm']
                    : ['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(fn (callable $get) => self::typeIs($get, MediaType::Video) ? 12288 : 5120)
                ->visible(fn (callable $get) => self::typeIs($get, MediaType::Image, MediaType::Video))
                ->required(fn (callable $get) => self::typeIs($get, MediaType::Image) || (self::typeIs($get, MediaType::Video) && blank($get('url'))))
                ->helperText(fn (callable $get) => self::typeIs($get, MediaType::Video)
                    ? 'Untuk video yang besar, lebih baik tampal pautan YouTube/Vimeo di bawah.'
                    : 'JPG, PNG atau WebP, maksimum 5MB.')
                ->columnSpanFull(),

            TextInput::make('url')->label(fn (callable $get) => self::typeIs($get, MediaType::Video) ? 'Pautan YouTube / Vimeo' : 'Pautan (URL)')
                ->url()
                ->maxLength(2048)
                ->placeholder('https://')
                ->visible(fn (callable $get) => self::typeIs($get, MediaType::Video, MediaType::Link))
                ->required(fn (callable $get) => self::typeIs($get, MediaType::Link) || (self::typeIs($get, MediaType::Video) && blank($get('file_path'))))
                ->rule(fn (callable $get) => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                    if (filled($value) && self::typeIs($get, MediaType::Video) && ! VideoEmbed::isSupported($value)) {
                        $fail('Hanya pautan YouTube atau Vimeo boleh dibenamkan sebagai video. Untuk laman lain, pilih jenis "Pautan".');
                    }
                })
                ->columnSpanFull(),

            Textarea::make('caption')->label('Keterangan')->rows(2)->columnSpanFull(),
            TextInput::make('credit')->label('Kredit / hak cipta')->placeholder('cth. Foto: Nama jurugambar, CC BY 4.0'),
            Select::make('source_id')->label('Sumber rujukan (pilihan)')
                ->options(fn () => Source::orderBy('title')->get()->pluck('display_name', 'id'))
                ->searchable(),
            Select::make('after_scene_id')->label('Papar selepas')
                ->options(fn () => $this->getOwnerRecord()->scenes()->with('latestRevision')->get()
                    ->mapWithKeys(fn ($s) => [$s->id => "Babak {$s->position}: ".Str::limit(strip_tags((string) $s->latestRevision?->body_ms), 50)]))
                ->placeholder('Selepas babak terakhir')
                ->columnSpanFull(),
            Checkbox::make('depiction_confirmed')
                ->label('Saya sahkan media ini TIDAK memaparkan Nabi ﷺ, para nabi, malaikat atau para sahabat.')
                ->accepted()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        $clean = function (array $data): array {
            $type = MediaType::tryFrom((string) ($data['type'] ?? ''));
            if ($type === MediaType::Image) {
                $data['url'] = null;
            }
            if ($type === MediaType::Link) {
                $data['file_path'] = null;
            }

            return [...$data, 'edited_by' => auth()->id()];
        };

        return $table
            ->reorderable('position')
            ->defaultSort('position')
            ->columns([
                ImageColumn::make('file_path')->label('')->disk('public')->square()->imageHeight(48)
                    ->state(fn (ChapterMedia $record) => $record->type === MediaType::Image ? $record->file_path : null),
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('title')->label('Tajuk')->wrap(),
                TextColumn::make('afterScene.position')->label('Selepas babak')->placeholder('Akhir'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->mutateFormDataUsing($clean),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()->canWrite())
                    ->mutateFormDataUsing($clean),
                ActionGroup::make([
                    ...WorkflowActions::all(),
                    DeleteAction::make()->visible(fn () => auth()->user()->canWrite()),
                ]),
            ]);
    }
}
