<?php

namespace App\Filament\App\Resources\Incidents\Pages;

use App\Filament\App\Resources\Incidents\ServiceIncidentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceIncident extends EditRecord
{
    protected static string $resource = ServiceIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
