<?php

namespace App\Filament\App\Resources\Checklists\Pages;

use App\Filament\App\Resources\Checklists\PreoperationalChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPreoperationalChecklists extends ListRecords
{
    protected static string $resource = PreoperationalChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
