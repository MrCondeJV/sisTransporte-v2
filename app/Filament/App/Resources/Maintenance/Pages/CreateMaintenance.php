<?php

namespace App\Filament\App\Resources\Maintenance\Pages;

use App\Filament\App\Resources\Maintenance\MaintenanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenance extends CreateRecord
{
    protected static string $resource = MaintenanceResource::class;

    protected function afterCreate(): void
    {
        $maintenance = $this->record;
        if ($maintenance->vehicle && $maintenance->mileage > $maintenance->vehicle->current_mileage) {
            $maintenance->vehicle->update(['current_mileage' => $maintenance->mileage]);
        }
    }
}
