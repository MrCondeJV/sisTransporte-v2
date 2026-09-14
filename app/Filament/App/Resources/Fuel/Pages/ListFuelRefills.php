<?php

namespace App\Filament\App\Resources\Fuel\Pages;

use App\Filament\App\Resources\Fuel\FuelRefillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFuelRefills extends ListRecords
{
    protected static string $resource = FuelRefillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
