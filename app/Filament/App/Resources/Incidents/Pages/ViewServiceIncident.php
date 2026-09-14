<?php

namespace App\Filament\App\Resources\Incidents\Pages;

use App\Filament\App\Resources\Incidents\ServiceIncidentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceIncident extends ViewRecord
{
    protected static string $resource = ServiceIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
