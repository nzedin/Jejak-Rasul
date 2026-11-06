<?php

namespace App\Services;

use App\Contracts\Revision;
use App\Enums\RevisionStatus;
use App\Exceptions\WorkflowException;
use App\Models\ChapterMedia;
use App\Models\Choice;
use App\Models\QuizQuestion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Aliran semakan untuk ChapterRevision & SceneRevision.
 *
 *   draf ──hantar──▶ dihantar ──lulus──▶ diluluskan (diterbitkan)
 *                       │
 *                       └──minta pindaan──▶ perlu_pindaan ──edit/hantar──▶ dihantar
 */
class ContentWorkflow
{
    /** Penulis hantar revision untuk disemak. */
    public function submit(Revision&Model $revision, User $user): void
    {
        if (! $user->canWrite()) {
            throw new WorkflowException('Hanya penulis atau admin boleh menghantar kandungan.');
        }

        if (! $revision->status->isEditable()) {
            throw new WorkflowException('Hanya draf atau kandungan yang perlu dipinda boleh dihantar.');
        }

        $revision->forceFill([
            'status' => RevisionStatus::Submitted,
            'submitted_at' => now(),
        ])->save();
    }

    /** Penyemak luluskan — revision ini terus menjadi versi yang dibaca pemain. */
    public function approve(Revision&Model $revision, User $reviewer, ?string $comment = null): void
    {
        $this->ensureCanReview($revision, $reviewer);

        if ($revision->status !== RevisionStatus::Submitted) {
            throw new WorkflowException('Hanya kandungan yang dihantar untuk semakan boleh diluluskan.');
        }

        DB::transaction(function () use ($revision, $reviewer, $comment) {
            $revision->forceFill([
                'status' => RevisionStatus::Approved,
                'approved_at' => now(),
            ])->save();

            $this->record($revision, $reviewer, Review::APPROVE, $comment);

            $revision->revisable()->forceFill(['published_revision_id' => $revision->id])->save();
        });
    }

    /** Penyemak minta pindaan. Komen wajib supaya penulis tahu apa nak dibetulkan. */
    public function requestChanges(Revision&Model $revision, User $reviewer, string $comment): void
    {
        $this->ensureCanReview($revision, $reviewer);

        if ($revision->status !== RevisionStatus::Submitted) {
            throw new WorkflowException('Hanya kandungan yang dihantar untuk semakan boleh diminta pindaan.');
        }

        if (trim($comment) === '') {
            throw new WorkflowException('Sila nyatakan apa yang perlu dipinda.');
        }

        DB::transaction(function () use ($revision, $reviewer, $comment) {
            $revision->forceFill(['status' => RevisionStatus::ChangesRequested])->save();
            $this->record($revision, $reviewer, Review::REQUEST_CHANGES, $comment);
        });
    }

    /**
     * Kembalikan versi lama yang PERNAH diluluskan sebagai versi diterbitkan.
     * Hanya penyemak/admin, kerana ia mengubah apa yang pemain baca.
     */
    public function restore(Revision&Model $revision, User $reviewer, ?string $comment = null): void
    {
        if (! $reviewer->canReview()) {
            throw new WorkflowException('Hanya penyemak atau admin boleh mengembalikan versi lama.');
        }

        if ($revision->status !== RevisionStatus::Approved) {
            throw new WorkflowException('Hanya versi yang pernah diluluskan boleh dikembalikan.');
        }

        DB::transaction(function () use ($revision, $reviewer, $comment) {
            $revision->revisable()->forceFill(['published_revision_id' => $revision->id])->save();
            $this->record($revision, $reviewer, Review::APPROVE, $comment ?? "Dikembalikan ke versi {$revision->version}");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Item kecil: pilihan Yusuf & soalan kuiz (App\Models\Concerns\HasSimpleReview)
    |--------------------------------------------------------------------------
    */

    public function submitItem(Choice|QuizQuestion|ChapterMedia $item, User $user): void
    {
        if (! $user->canWrite()) {
            throw new WorkflowException('Hanya penulis atau admin boleh menghantar kandungan.');
        }

        if (! $item->status->isEditable()) {
            throw new WorkflowException('Hanya draf atau kandungan yang perlu dipinda boleh dihantar.');
        }

        $item->forceFill(['status' => RevisionStatus::Submitted])->saveQuietly();
    }

    public function approveItem(Choice|QuizQuestion|ChapterMedia $item, User $reviewer, ?string $comment = null): void
    {
        $this->ensureCanReview($item, $reviewer);

        if ($item->status !== RevisionStatus::Submitted) {
            throw new WorkflowException('Hanya kandungan yang dihantar untuk semakan boleh diluluskan.');
        }

        DB::transaction(function () use ($item, $reviewer, $comment) {
            $item->forceFill(['status' => RevisionStatus::Approved])->saveQuietly();
            $this->record($item, $reviewer, Review::APPROVE, $comment);
        });
    }

    public function requestChangesItem(Choice|QuizQuestion|ChapterMedia $item, User $reviewer, string $comment): void
    {
        $this->ensureCanReview($item, $reviewer);

        if ($item->status !== RevisionStatus::Submitted || trim($comment) === '') {
            throw new WorkflowException('Hanya kandungan yang dihantar boleh diminta pindaan, dan komen wajib diisi.');
        }

        DB::transaction(function () use ($item, $reviewer, $comment) {
            $item->forceFill(['status' => RevisionStatus::ChangesRequested])->saveQuietly();
            $this->record($item, $reviewer, Review::REQUEST_CHANGES, $comment);
        });
    }

    private function ensureCanReview(Model $revision, User $reviewer): void
    {
        if (! $reviewer->canReview()) {
            throw new WorkflowException('Hanya penyemak atau admin boleh menyemak kandungan.');
        }

        if ($revision->isAuthoredBy($reviewer)) {
            throw new WorkflowException('Anda tidak boleh menyemak kandungan yang anda tulis sendiri.');
        }
    }

    private function record(Model $revision, User $reviewer, string $decision, ?string $comment): void
    {
        $revision->reviews()->create([
            'reviewer_id' => $reviewer->id,
            'decision' => $decision,
            'comment' => $comment,
        ]);
    }
}
