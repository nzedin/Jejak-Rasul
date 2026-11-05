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
}
