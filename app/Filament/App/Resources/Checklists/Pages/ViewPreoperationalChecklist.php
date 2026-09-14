<?php

namespace App\Filament\App\Resources\Checklists\Pages;

use App\Filament\App\Resources\Checklists\PreoperationalChecklistResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPreoperationalChecklist extends ViewRecord
{
    protected static string $resource = PreoperationalChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
