<?php

namespace App\Filament\App\Resources\Fuel\Pages;

use App\Filament\App\Resources\Fuel\FuelRefillResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuelRefill extends ViewRecord
{
    protected static string $resource = FuelRefillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
