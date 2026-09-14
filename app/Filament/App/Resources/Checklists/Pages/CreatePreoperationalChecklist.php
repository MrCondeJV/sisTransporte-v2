<?php

namespace App\Filament\App\Resources\Checklists\Pages;

use App\Filament\App\Resources\Checklists\PreoperationalChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePreoperationalChecklist extends CreateRecord
{
    protected static string $resource = PreoperationalChecklistResource::class;

    protected function afterCreate(): void
    {
        $checklist = $this->record;
        if ($checklist->vehicle && $checklist->mileage > $checklist->vehicle->current_mileage) {
            $checklist->vehicle->update(['current_mileage' => $checklist->mileage]);
        }
    }
}
