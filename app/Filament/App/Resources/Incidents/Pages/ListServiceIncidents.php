<?php

namespace App\Filament\App\Resources\Incidents\Pages;

use App\Filament\App\Resources\Incidents\ServiceIncidentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceIncidents extends ListRecords
{
    protected static string $resource = ServiceIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
