<?php

namespace App\Filament\App\Resources\ServiceOrders\Pages;

use App\Filament\App\Resources\ServiceOrders\ServiceOrderResource;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\OperationValidationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceOrder extends EditRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $vehicle = ! empty($this->data['vehicle_id']) ? Vehicle::find($this->data['vehicle_id']) : null;
        $driver = ! empty($this->data['driver_id']) ? Employee::find($this->data['driver_id']) : null;
        $supportDriver = ! empty($this->data['support_driver_id']) ? Employee::find($this->data['support_driver_id']) : null;

        app(OperationValidationService::class)->validateAssignment($vehicle, $driver, $supportDriver);
    }
}
