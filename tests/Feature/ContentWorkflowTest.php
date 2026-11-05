<?php

namespace Tests\Feature;

use App\Enums\RevisionStatus;
use App\Enums\SceneLabel;
use App\Exceptions\WorkflowException;
use App\Models\Arc;
use App\Models\QuizQuestion;
use App\Models\Source;
use App\Models\User;
use App\Services\ChapterEditor;
use App\Services\ContentWorkflow;
use App\Services\SceneEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $writer;

    private User $reviewer;

    private Arc $arc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = User::factory()->writer()->create();
        $this->reviewer = User::factory()->reviewer()->create();
        $this->arc = Arc::create(['title' => 'Hijrah', 'slug' => 'hijrah']);
    }

    private function chapters(): ChapterEditor
    {
        return app(ChapterEditor::class);
    }

    private function scenes(): SceneEditor
    {
        return app(SceneEditor::class);
    }

    private function workflow(): ContentWorkflow
    {
        return app(ContentWorkflow::class);
    }

    public function test_new_chapter_starts_as_unpublished_draft(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'Gua Thur'], $this->writer);

        $this->assertFalse($chapter->isPublished());
        $this->assertSame(RevisionStatus::Draft, $chapter->latestRevision->status);
        $this->assertSame(1, $chapter->latestRevision->version);
    }

    public function test_approved_title_stays_live_until_new_version_is_approved(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'Tajuk asal'], $this->writer);
        $v1 = $chapter->latestRevision;
        $this->workflow()->submit($v1, $this->writer);
        $this->workflow()->approve($v1->fresh(), $this->reviewer);

        // Penulis ubah tajuk selepas jumpa sumber yang lebih sahih
        $v2 = $this->chapters()->edit($chapter->fresh(), ['title' => 'Tajuk dibetulkan'], $this->writer, 'Ikut riwayat Bukhari');

        $this->assertSame(2, $v2->version);
        $this->assertSame('Tajuk asal', $chapter->fresh()->publishedRevision->title, 'Pemain masih nampak versi lama');

        $this->workflow()->submit($v2, $this->writer);
        $this->workflow()->approve($v2->fresh(), $this->reviewer);

        $this->assertSame('Tajuk dibetulkan', $chapter->fresh()->publishedRevision->title);
        $this->assertCount(2, $chapter->fresh()->revisions);
    }

    public function test_editing_a_draft_updates_the_same_version(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $this->writer);
        $this->chapters()->edit($chapter, ['title' => 'B'], $this->writer);

        $this->assertCount(1, $chapter->fresh()->revisions);
        $this->assertSame('B', $chapter->fresh()->latestRevision->title);
    }

    public function test_editing_a_submitted_version_pulls_it_back_to_draft(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $this->writer);
        $this->workflow()->submit($chapter->latestRevision, $this->writer);

        $revision = $this->chapters()->edit($chapter->fresh(), ['title' => 'A2'], $this->writer);

        $this->assertSame(RevisionStatus::Draft, $revision->status);
        $this->assertNull($revision->submitted_at);
    }

    public function test_reviewer_cannot_approve_own_edits(): void
    {
        $admin = User::factory()->admin()->create();
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $admin);
        $this->workflow()->submit($chapter->latestRevision, $admin);

        $this->expectException(WorkflowException::class);
        $this->workflow()->approve($chapter->latestRevision->fresh(), $admin);
    }

    public function test_writer_cannot_approve(): void
    {
        $other = User::factory()->writer()->create();
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $this->writer);
        $this->workflow()->submit($chapter->latestRevision, $this->writer);

        $this->expectException(WorkflowException::class);
        $this->workflow()->approve($chapter->latestRevision->fresh(), $other);
    }

    public function test_only_submitted_content_can_be_approved(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $this->writer);

        $this->expectException(WorkflowException::class);
        $this->workflow()->approve($chapter->latestRevision, $this->reviewer);
    }

    public function test_request_changes_requires_comment_and_is_recorded(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'A'], $this->writer);
        $revision = $chapter->latestRevision;
        $this->workflow()->submit($revision, $this->writer);

        $this->workflow()->requestChanges($revision->fresh(), $this->reviewer, 'Sila semak sanad');

        $revision->refresh();
        $this->assertSame(RevisionStatus::ChangesRequested, $revision->status);
        $this->assertSame('Sila semak sanad', $revision->reviews->first()->comment);
    }

    public function test_scene_story_and_sources_are_versioned(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'Gua Thur'], $this->writer);
        $bukhari = Source::create(['type' => 'hadis', 'title' => 'Sahih al-Bukhari']);
        $sirah = Source::create(['type' => 'kitab_sirah', 'title' => 'Sirah Ibn Hisham']);

        $scene = $this->scenes()->create($chapter, ['body_ms' => '<p>Versi 1</p>', 'label' => SceneLabel::Riwayat], [$sirah->id], $this->writer);
        $v1 = $scene->latestRevision;
        $this->workflow()->submit($v1, $this->writer);
        $this->workflow()->approve($v1->fresh(), $this->reviewer);

        $v2 = $this->scenes()->edit($scene->fresh(), ['body_ms' => '<p>Versi 2</p>', 'label' => SceneLabel::Sahih], [$bukhari->id], $this->writer);

        $this->assertSame(2, $v2->version);
        $this->assertSame(SceneLabel::Riwayat, $scene->fresh()->publishedRevision->label);
        $this->assertSame([$sirah->id], $v1->fresh()->sources->pluck('id')->all(), 'Sumber versi lama kekal');
        $this->assertSame([$bukhari->id], $v2->sources->pluck('id')->all());
    }

    public function test_restore_republishes_older_approved_version(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'Lama'], $this->writer);
        $v1 = $chapter->latestRevision;
        $this->workflow()->submit($v1, $this->writer);
        $this->workflow()->approve($v1->fresh(), $this->reviewer);

        $v2 = $this->chapters()->edit($chapter->fresh(), ['title' => 'Baru'], $this->writer);
        $this->workflow()->submit($v2, $this->writer);
        $this->workflow()->approve($v2->fresh(), $this->reviewer);

        $this->workflow()->restore($v1->fresh(), $this->reviewer);

        $this->assertSame('Lama', $chapter->fresh()->publishedRevision->title);
    }

    public function test_editing_an_approved_quiz_question_hides_it_until_reviewed_again(): void
    {
        $chapter = $this->chapters()->create($this->arc, ['title' => 'Gua Thur'], $this->writer);
        $quiz = QuizQuestion::create([
            'chapter_id' => $chapter->id,
            'statement' => 'Kenyataan',
            'answer' => 'lemah',
            'explanation' => 'Penerangan',
            'edited_by' => $this->writer->id,
        ]);

        $this->workflow()->submitItem($quiz, $this->writer);
        $this->workflow()->approveItem($quiz->fresh(), $this->reviewer);
        $this->assertSame(RevisionStatus::Approved, $quiz->fresh()->status);

        $quiz = $quiz->fresh();
        $quiz->update(['explanation' => 'Penerangan baru']);

        $this->assertSame(RevisionStatus::Draft, $quiz->fresh()->status);
    }
}
