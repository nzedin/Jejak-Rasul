<?php

namespace Tests\Feature;

use App\Enums\SceneLabel;
use App\Models\Arc;
use App\Models\User;
use App\Services\ChapterEditor;
use App\Services\ContentWorkflow;
use App\Services\SceneEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_players_only_see_approved_content(): void
    {
        $writer = User::factory()->writer()->create();
        $reviewer = User::factory()->reviewer()->create();
        $wf = app(ContentWorkflow::class);

        $arc = Arc::create(['title' => 'Hijrah', 'slug' => 'hijrah', 'is_published' => true]);
        $chapter = app(ChapterEditor::class)->create($arc, ['title' => 'Bab diluluskan'], $writer);
        $wf->submit($chapter->latestRevision, $writer);
        $wf->approve($chapter->latestRevision->fresh(), $reviewer);

        $scene = app(SceneEditor::class)->create($chapter, ['body_ms' => '<p>Babak diluluskan</p>', 'label' => SceneLabel::Rekaan], [], $writer);
        $wf->submit($scene->latestRevision, $writer);
        $wf->approve($scene->latestRevision->fresh(), $reviewer);
        app(SceneEditor::class)->create($chapter, ['body_ms' => '<p>Babak draf</p>', 'label' => SceneLabel::Rekaan], [], $writer);

        $this->get('/')->assertOk()->assertSee('Hijrah');
        $this->get('/arka/hijrah')->assertOk()->assertSee('Bab diluluskan');
        $this->get("/arka/hijrah/bab/{$chapter->id}")
            ->assertOk()
            ->assertSee('Babak diluluskan')
            ->assertDontSee('Babak draf');

        // Tetamu tidak boleh guna mod pratonton
        $this->get("/arka/hijrah/bab/{$chapter->id}?pratonton=1")->assertDontSee('Babak draf');

        // Penulis boleh pratonton draf
        $this->actingAs($writer)
            ->get("/arka/hijrah/bab/{$chapter->id}?pratonton=1")
            ->assertSee('Babak draf')
            ->assertSee('Mod pratonton');
    }

    public function test_unpublished_arc_and_chapter_return_404(): void
    {
        $writer = User::factory()->writer()->create();
        $arc = Arc::create(['title' => 'Draf', 'slug' => 'draf', 'is_published' => false]);
        $chapter = app(ChapterEditor::class)->create($arc, ['title' => 'Bab draf'], $writer);

        $this->get('/arka/draf')->assertNotFound();

        $arc->update(['is_published' => true]);
        $this->get("/arka/draf/bab/{$chapter->id}")->assertNotFound();
    }

    public function test_content_under_review_is_hidden_from_players(): void
    {
        $writer = User::factory()->writer()->create();
        $reviewer = User::factory()->reviewer()->create();
        $wf = app(ContentWorkflow::class);

        $arc = Arc::create(['title' => 'Hijrah', 'slug' => 'hijrah', 'is_published' => true]);
        $chapter = app(ChapterEditor::class)->create($arc, ['title' => 'Tajuk lulus'], $writer);
        $wf->submit($chapter->latestRevision, $writer);
        $wf->approve($chapter->latestRevision->fresh(), $reviewer);

        // Versi baru tajuk sedang DIHANTAR untuk semakan
        $v2 = app(ChapterEditor::class)->edit($chapter->fresh(), ['title' => 'Tajuk dalam semakan'], $writer);
        $wf->submit($v2, $writer);

        // Babak yang sedang DIHANTAR & babak yang PERLU PINDAAN
        $submitted = app(SceneEditor::class)->create($chapter, ['body_ms' => '<p>Babak dihantar</p>', 'label' => SceneLabel::Rekaan], [], $writer);
        $wf->submit($submitted->latestRevision, $writer);
        $rejected = app(SceneEditor::class)->create($chapter, ['body_ms' => '<p>Babak perlu pindaan</p>', 'label' => SceneLabel::Rekaan], [], $writer);
        $wf->submit($rejected->latestRevision, $writer);
        $wf->requestChanges($rejected->latestRevision->fresh(), $reviewer, 'Semak semula');

        $this->get('/arka/hijrah')->assertSee('Tajuk lulus')->assertDontSee('Tajuk dalam semakan');
        $this->get("/arka/hijrah/bab/{$chapter->id}")
            ->assertSee('Tajuk lulus')
            ->assertDontSee('Tajuk dalam semakan')
            ->assertDontSee('Babak dihantar')
            ->assertDontSee('Babak perlu pindaan');
    }

    public function test_published_arc_without_approved_chapters_is_hidden(): void
    {
        $writer = User::factory()->writer()->create();
        $arc = Arc::create(['title' => 'Arka kosong', 'slug' => 'kosong', 'is_published' => true]);
        app(ChapterEditor::class)->create($arc, ['title' => 'Draf'], $writer);

        $this->get('/')->assertOk()->assertDontSee('Arka kosong');
    }

    public function test_only_approved_media_is_embedded(): void
    {
        $writer = User::factory()->writer()->create();
        $reviewer = User::factory()->reviewer()->create();
        $wf = app(ContentWorkflow::class);

        $arc = Arc::create(['title' => 'Hijrah', 'slug' => 'hijrah', 'is_published' => true]);
        $chapter = app(ChapterEditor::class)->create($arc, ['title' => 'Bab'], $writer);
        $wf->submit($chapter->latestRevision, $writer);
        $wf->approve($chapter->latestRevision->fresh(), $reviewer);

        $approved = \App\Models\ChapterMedia::create(['chapter_id' => $chapter->id, 'type' => 'video', 'title' => 'Video lulus', 'url' => 'https://youtu.be/dQw4w9WgXcQ', 'edited_by' => $writer->id]);
        $wf->submitItem($approved, $writer);
        $wf->approveItem($approved->fresh(), $reviewer);
        \App\Models\ChapterMedia::create(['chapter_id' => $chapter->id, 'type' => 'pautan', 'title' => 'Pautan draf', 'url' => 'https://example.com', 'edited_by' => $writer->id]);

        $this->get("/arka/hijrah/bab/{$chapter->id}")
            ->assertSee('Video lulus')
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertDontSee('Pautan draf');
    }
}
