<?php

namespace App\Filament\Resources\ChapterRevisions\Pages;

use App\Filament\Resources\ChapterRevisions\ChapterRevisionResource;
use Filament\Resources\Pages\ListRecords;

class ListChapterRevisions extends ListRecords
{
    protected static string $resource = ChapterRevisionResource::class;
}
