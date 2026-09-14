<?php

namespace App\Filament\App\Resources\Fuec\Pages;

use App\Filament\App\Resources\Fuec\FuecDocumentResource;
use App\Models\Tenant\FuecDocument;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewFuecDocument extends ViewRecord
{
    protected static string $resource = FuecDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->url(fn (FuecDocument $record) => route('tenant.fuec.download', ['fuec_number' => $record->fuec_number]))
                ->openUrlInNewTab(),

            Action::make('verify_online')
                ->label('Validar QR en Portal')
                ->icon(Heroicon::OutlinedQrCode)
                ->color('info')
                ->url(fn (FuecDocument $record) => route('tenant.fuec.verify', ['fuec_number' => $record->fuec_number]))
                ->openUrlInNewTab(),
        ];
    }
}
