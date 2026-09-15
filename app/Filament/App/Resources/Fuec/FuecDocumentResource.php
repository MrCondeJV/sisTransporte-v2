<?php

namespace App\Filament\App\Resources\Fuec;

use App\Filament\App\Resources\Fuec\Pages\ListFuecDocuments;
use App\Filament\App\Resources\Fuec\Pages\ViewFuecDocument;
use App\Models\Tenant\FuecDocument;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FuecDocumentResource extends Resource
{
    protected static ?string $model = FuecDocument::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Operaciones y Servicios';

    protected static ?string $navigationLabel = 'Documentos FUEC';

    protected static ?string $modelLabel = 'Documento FUEC';

    protected static ?string $pluralModelLabel = 'Documentos FUEC';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del FUEC Oficial')
                ->description('Datos reglamentarios según Resolución MinTransporte')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('fuec_number')
                            ->label('Número Oficial FUEC')
                            ->disabled(),

                        TextInput::make('resolution_number')
                            ->label('N° Resolución Habilitación')
                            ->disabled(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Emitido' => 'Emitido',
                                'Anulado' => 'Anulado',
                                'Vencido' => 'Vencido',
                            ])
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('issue_date')
                            ->label('Fecha de Expedición')
                            ->disabled(),

                        DatePicker::make('expiration_date')
                            ->label('Fecha de Vencimiento')
                            ->disabled(),
                    ]),

                    TextInput::make('qr_code_content')
                        ->label('Enlace de Verificación QR')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fuec_number')
                    ->label('N° FUEC')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('serviceOrder.order_number')
                    ->label('Orden de Servicio')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('serviceOrder.client.business_name')
                    ->label('Cliente / Contratante')
                    ->limit(25)
                    ->searchable(),

                TextColumn::make('serviceOrder.vehicle.plate')
                    ->label('Vehículo')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('issue_date')
                    ->label('Expedido')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('expiration_date')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Emitido' => 'success',
                        'Anulado' => 'danger',
                        'Vencido' => 'warning',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Emitido' => 'Emitido',
                        'Anulado' => 'Anulado',
                        'Vencido' => 'Vencido',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('download_pdf')
                    ->label('Descargar PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->url(fn (FuecDocument $record) => route('tenant.fuec.download', ['fuec_number' => $record->fuec_number]))
                    ->openUrlInNewTab(),

                Action::make('verify_online')
                    ->label('Validar QR')
                    ->icon(Heroicon::OutlinedQrCode)
                    ->color('info')
                    ->url(fn (FuecDocument $record) => route('tenant.fuec.verify', ['fuec_number' => $record->fuec_number]))
                    ->openUrlInNewTab(),

                Action::make('cancel_fuec')
                    ->label('Anular')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Anular Documento FUEC?')
                    ->modalDescription('El FUEC quedará revocado permanentemente ante cualquier verificación de autoridades.')
                    ->visible(fn (FuecDocument $record) => $record->status === 'Emitido')
                    ->action(function (FuecDocument $record) {
                        $record->update(['status' => 'Anulado']);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFuecDocuments::route('/'),
            'view' => ViewFuecDocument::route('/{record}'),
        ];
    }
}
