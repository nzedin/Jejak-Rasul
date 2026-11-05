<?php

namespace App\Http\Controllers;

use App\Models\Arc;
use App\Models\Chapter;
use App\Services\PlayerContent;
use Illuminate\Http\Request;

class PlayerController
{
    private function content(Request $request): PlayerContent
    {
        return PlayerContent::for($request->user(), $request->boolean('pratonton'));
    }

    public function home(Request $request)
    {
        $content = $this->content($request);

        return view('player.home', [
            'arcs' => $content->arcs(),
            'preview' => $content->preview,
        ]);
    }

    public function arc(Request $request, Arc $arc)
    {
        $content = $this->content($request);
        abort_unless($content->canSeeArc($arc), 404);

        return view('player.arc', [
            'arc' => $arc,
            'chapters' => $content->chapters($arc),
            'preview' => $content->preview,
        ]);
    }

    public function chapter(Request $request, Arc $arc, Chapter $chapter)
    {
        $content = $this->content($request);
        abort_unless($content->canSeeArc($arc) && $chapter->arc_id === $arc->id, 404);

        $chapters = $content->chapters($arc);
        $index = $chapters->search(fn (Chapter $c) => $c->is($chapter));
        abort_if($index === false, 404); // bab belum diterbitkan

        $current = $chapters[$index];
        $scenes = $content->scenes($current);

        return view('player.chapter', [
            'arc' => $arc,
            'chapter' => $current,
            'number' => $index + 1,
            'total' => $chapters->count(),
            'scenes' => $scenes,
            'sources' => $scenes->flatMap(fn ($s) => $s->revision->sources)->unique('id')->values(),
            'choice' => $content->choice($current),
            'quiz' => $content->quiz($current),
            'previous' => $chapters[$index - 1] ?? null,
            'next' => $chapters[$index + 1] ?? null,
            'preview' => $content->preview,
        ]);
    }
}
