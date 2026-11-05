<?php

namespace App\Filament\Actions;

use App\Contracts\Revision;
use App\Enums\RevisionStatus;
use App\Exceptions\WorkflowException;
use App\Models\Choice;
use App\Models\QuizQuestion;
use App\Services\ContentWorkflow;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * Butang aliran semakan yang dikongsi oleh semua jadual/halaman:
 * Hantar → Lulus / Minta pindaan → Kembalikan versi.
 *
 * $resolve: tukar rekod jadual kepada objek yang disemak (cth. Scene → latestRevision).
 */
class WorkflowActions
{
    public static function submit(?Closure $resolve = null): Action
    {
        return Action::make('submit')
            ->label('Hantar untuk semakan')
            ->icon(Heroicon::PaperAirplane)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Model $record) => auth()->user()->canWrite()
                && static::target($record, $resolve)?->status->isEditable())
            ->action(fn (Model $record) => static::run(
                fn (ContentWorkflow $w, $target) => static::isItem($target)
                    ? $w->submitItem($target, auth()->user())
                    : $w->submit($target, auth()->user()),
                static::target($record, $resolve),
                'Dihantar untuk semakan.',
            ));
    }

    public static function approve(?Closure $resolve = null): Action
    {
        return Action::make('approve')
            ->label('Lulus')
            ->icon(Heroicon::Check)
            ->color('success')
            ->visible(fn (Model $record) => static::canReview($record, $resolve))
            ->schema([
                Textarea::make('comment')->label('Komen (pilihan)')->rows(3),
            ])
            ->modalHeading('Luluskan kandungan ini?')
            ->modalSubmitActionLabel('Lulus & terbitkan')
            ->action(fn (Model $record, array $data) => static::run(
                fn (ContentWorkflow $w, $target) => static::isItem($target)
                    ? $w->approveItem($target, auth()->user(), $data['comment'] ?? null)
                    : $w->approve($target, auth()->user(), $data['comment'] ?? null),
                static::target($record, $resolve),
                'Diluluskan. Pemain kini nampak versi ini.',
            ));
    }

    public static function requestChanges(?Closure $resolve = null): Action
    {
        return Action::make('requestChanges')
            ->label('Minta pindaan')
            ->icon(Heroicon::XMark)
            ->color('danger')
            ->visible(fn (Model $record) => static::canReview($record, $resolve))
            ->schema([
                Textarea::make('comment')->label('Apa yang perlu dipinda?')->required()->rows(4),
            ])
            ->action(fn (Model $record, array $data) => static::run(
                fn (ContentWorkflow $w, $target) => static::isItem($target)
                    ? $w->requestChangesItem($target, auth()->user(), $data['comment'])
                    : $w->requestChanges($target, auth()->user(), $data['comment']),
                static::target($record, $resolve),
                'Dihantar semula kepada penulis untuk dipinda.',
            ));
    }

    /** Untuk jadual sejarah revision: kembalikan versi lama yang pernah diluluskan. */
    public static function restore(): Action
    {
        return Action::make('restore')
            ->label('Kembalikan versi ini')
            ->icon(Heroicon::ArrowUturnLeft)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Pemain akan terus nampak versi ini menggantikan versi semasa.')
            ->visible(fn (Model $record) => auth()->user()->canReview()
                && $record instanceof Revision
                && $record->status === RevisionStatus::Approved
                && ! $record->isPublished())
            ->action(fn (Model $record) => static::run(
                fn (ContentWorkflow $w, $target) => $w->restore($target, auth()->user()),
                $record,
                "Versi {$record->version} dikembalikan.",
            ));
    }

    /**
     * Papar perbandingan versi (yang disemak vs yang diterbitkan).
     *
     * @param  array<string, string>  $fields  medan => label
     */
    public static function compare(array $fields, ?Closure $resolve = null): Action
    {
        return Action::make('compare')
            ->label('Bandingkan')
            ->icon(Heroicon::OutlinedDocumentText)
            ->color('gray')
            ->modalHeading('Perbandingan versi')
            ->modalWidth('4xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->visible(fn (Model $record) => static::target($record, $resolve) instanceof Revision)
            ->modalContent(function (Model $record) use ($fields, $resolve) {
                /** @var Revision&Model $revision */
                $revision = static::target($record, $resolve);
                $published = $revision->revisable()->publishedRevision;

                return view('filament.review.compare', [
                    'revision' => $revision,
                    'published' => $published && $published->isNot($revision) ? $published : null,
                    'isLive' => $published?->is($revision) ?? false,
                    'fields' => $fields,
                ]);
            });
    }

    public static function all(?Closure $resolve = null): array
    {
        return [
            static::submit($resolve),
            static::approve($resolve),
            static::requestChanges($resolve),
        ];
    }

    private static function target(Model $record, ?Closure $resolve): ?Model
    {
        return $resolve ? $resolve($record) : $record;
    }

    private static function isItem(Model $target): bool
    {
        return $target instanceof Choice || $target instanceof QuizQuestion;
    }

    private static function canReview(Model $record, ?Closure $resolve): bool
    {
        $target = static::target($record, $resolve);
        $user = auth()->user();

        return $target !== null
            && $user->canReview()
            && $target->status === RevisionStatus::Submitted
            && ! $target->isAuthoredBy($user);
    }

    private static function run(Closure $callback, ?Model $target, string $success): void
    {
        if ($target === null) {
            return;
        }

        try {
            $callback(app(ContentWorkflow::class), $target);
            Notification::make()->title($success)->success()->send();
        } catch (WorkflowException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
