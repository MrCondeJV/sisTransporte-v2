<?php

namespace App\Filament\App\Resources\ServiceOrders\Pages;

use App\Filament\App\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceOrder extends ViewRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
