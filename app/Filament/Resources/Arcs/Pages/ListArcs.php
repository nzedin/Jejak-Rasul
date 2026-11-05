<?php

namespace App\Filament\Resources\Arcs\Pages;

use App\Filament\Resources\Arcs\ArcResource;
use Filament\Resources\Pages\ListRecords;

class ListArcs extends ListRecords
{
    protected static string $resource = ArcResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
