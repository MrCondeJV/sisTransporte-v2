<?php

namespace App\Filament\App\Resources\Fuel\Pages;

use App\Filament\App\Resources\Fuel\FuelRefillResource;
use App\Services\Tenant\FuelPerformanceCalculator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFuelRefill extends EditRecord
{
    protected static string $resource = FuelRefillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(FuelPerformanceCalculator::class)->processAndSync($this->record);
    }
}
