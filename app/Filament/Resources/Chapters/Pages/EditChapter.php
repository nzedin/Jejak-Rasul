<?php

namespace App\Filament\Resources\Chapters\Pages;

use App\Exceptions\WorkflowException;
use App\Filament\Actions\WorkflowActions;
use App\Filament\Resources\Chapters\ChapterResource;
use App\Models\Chapter;
use App\Services\ChapterEditor;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

/**
 * Menyimpan TIDAK mengubah versi yang dibaca pemain. Ia mencipta/mengemas kini
 * versi draf, yang perlu dihantar & diluluskan dahulu.
 *
 * @property Chapter $record
 */
class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        $latest = $this->record->latestRevision;
        $published = $this->record->publishedRevision;

        return sprintf(
            'Anda sedang mengedit versi %d (%s). Pemain nampak: %s.',
            $latest?->version ?? 1,
            $latest?->status->getLabel() ?? 'Draf',
            $published ? "versi {$published->version}" : 'belum diterbitkan',
        );
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $latest = $this->record->latestRevision;

        return [
            ...$data,
            'title' => $latest?->title,
            'summary' => $latest?->summary,
            'change_note' => null,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(ChapterEditor::class)->edit($record, $data, auth()->user(), $data['change_note'] ?? null);

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Draf disimpan. Hantar untuk semakan supaya pemain nampak perubahan ini.';
    }

    protected function getHeaderActions(): array
    {
        return WorkflowActions::all(fn (Chapter $c) => $c->latestRevision);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        } catch (WorkflowException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
