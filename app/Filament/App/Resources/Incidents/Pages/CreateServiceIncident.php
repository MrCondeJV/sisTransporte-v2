<?php

namespace App\Filament\App\Resources\Incidents\Pages;

use App\Filament\App\Resources\Incidents\ServiceIncidentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceIncident extends CreateRecord
{
    protected static string $resource = ServiceIncidentResource::class;
}
