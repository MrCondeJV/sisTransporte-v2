<?php

namespace App\Filament\App\Resources\Fuel\Pages;

use App\Filament\App\Resources\Fuel\FuelRefillResource;
use App\Services\Tenant\FuelPerformanceCalculator;
use Filament\Resources\Pages\CreateRecord;

class CreateFuelRefill extends CreateRecord
{
    protected static string $resource = FuelRefillResource::class;

    protected function afterCreate(): void
    {
        app(FuelPerformanceCalculator::class)->processAndSync($this->record);
    }
}
