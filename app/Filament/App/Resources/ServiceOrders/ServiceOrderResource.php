<?php

namespace App\Filament\App\Resources\ServiceOrders;

use App\Filament\App\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\App\Resources\ServiceOrders\Pages\EditServiceOrder;
use App\Filament\App\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Filament\App\Resources\ServiceOrders\Pages\ViewServiceOrder;
use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\FuecGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Operaciones y Servicios';

    protected static ?string $navigationLabel = 'Órdenes de Servicio';

    protected static ?string $modelLabel = 'Orden de Servicio';

    protected static ?string $pluralModelLabel = 'Órdenes de Servicio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Servicio')
                ->description('Detalles generales, cliente y contrato')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('order_number')
                            ->label('Número de Orden')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'OS-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4))),

                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'business_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),

                        Select::make('contract_id')
                            ->label('Contrato de Transporte')
                            ->relationship('contract', 'contract_number')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional. Seleccione si el servicio está amparado bajo un contrato'),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('origin')
                            ->label('Origen')
                            ->placeholder('Ej: Bogotá D.C. / Terminal Salitre')
                            ->required(),

                        TextInput::make('destination')
                            ->label('Destino')
                            ->placeholder('Ej: Melgar, Tolima / Hotel Campestre')
                            ->required(),

                        TextInput::make('route_name')
                            ->label('Nombre de Ruta / Recorrido')
                            ->placeholder('Ej: Ruta Corporativa Norte - Centro'),
                    ]),

                    Grid::make(2)->schema([
                        DateTimePicker::make('scheduled_start_time')
                            ->label('Fecha y Hora Programada de Inicio')
                            ->required(),

                        DateTimePicker::make('scheduled_end_time')
                            ->label('Fecha y Hora Programada de Fin')
                            ->required(),
                    ]),
                ]),

            Section::make('Asignación de Flota y Personal')
                ->description('Validación de vehículos y conductores con documentación vigente')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('vehicle_id')
                            ->label('Vehículo Asignado')
                            ->relationship('vehicle', 'plate')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Vehicle $record) => "{$record->plate} - {$record->brand} {$record->line} ({$record->passenger_capacity} pax) - [Doc: {$record->document_status}]")
                            ->helperText('Solo vehículos con documentos al día deben ser asignados'),

                        Select::make('driver_id')
                            ->label('Conductor Principal')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Employee $record) => "{$record->name} - Lic: {$record->driver_license_category} [{$record->license_status}]")
                            ->helperText('Solo conductores con licencia vigente'),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('support_driver_id')
                            ->label('Conductor de Apoyo / Relevo')
                            ->relationship('supportDriver', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional para rutas largas superiores a 8 horas'),

                        Select::make('partner_id')
                            ->label('Aliado Propietario (Si aplica)')
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                ]),

            Section::make('Pasajeros y Estado de la Operación')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('passenger_contact_name')
                            ->label('Contacto del Pasajero / Encargado')
                            ->placeholder('Nombre completo del responsable en ruta'),

                        TextInput::make('passenger_contact_phone')
                            ->label('Teléfono de Contacto')
                            ->tel(),

                        TextInput::make('passengers_count')
                            ->label('Número de Pasajeros')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('status')
                            ->label('Estado de la Orden')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Asignada' => 'Asignada',
                                'En Progreso' => 'En Progreso',
                                'Finalizada' => 'Finalizada',
                                'Cancelada' => 'Cancelada',
                            ])
                            ->default('Pendiente')
                            ->required(),

                        TextInput::make('start_mileage')
                            ->label('Kilometraje Inicial')
                            ->numeric(),

                        TextInput::make('end_mileage')
                            ->label('Kilometraje Final')
                            ->numeric(),
                    ]),

                    Textarea::make('service_notes')
                        ->label('Observaciones / Instrucciones Especiales')
                        ->columnSpanFull(),
                ]),

            Section::make('Soporte de Facturación')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('invoice_number')
                            ->label('Número de Factura Electrónica'),

                        FileUpload::make('invoice_file')
                            ->label('Copia de Factura (PDF / Imagen)')
                            ->directory('invoices')
                            ->acceptedFileTypes(['application/pdf', 'image/*']),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('client.business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('origin')
                    ->label('Ruta')
                    ->formatStateUsing(fn ($record) => "{$record->origin} ➔ {$record->destination}")
                    ->limit(35),

                TextColumn::make('scheduled_start_time')
                    ->label('Inicio Programado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('vehicle.plate')
                    ->label('Vehículo')
                    ->badge()
                    ->color('primary')
                    ->default('Sin Asignar'),

                TextColumn::make('driver.name')
                    ->label('Conductor')
                    ->limit(20)
                    ->default('Sin Asignar'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'Asignada' => 'info',
                        'En Progreso' => 'warning',
                        'Finalizada' => 'success',
                        'Cancelada' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Asignada' => 'Asignada',
                        'En Progreso' => 'En Progreso',
                        'Finalizada' => 'Finalizada',
                        'Cancelada' => 'Cancelada',
                    ]),

                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'business_name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('emitir_fuec')
                    ->label('Emitir FUEC')
                    ->icon(Heroicon::OutlinedDocumentCheck)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('¿Emitir FUEC Oficial?')
                    ->modalDescription('Se generará el Formato Único de Extracto de Contrato con número consecutivo oficial y código QR de validación.')
                    ->visible(fn (ServiceOrder $record) => ! $record->fuec && $record->vehicle_id && $record->driver_id)
                    ->action(function (ServiceOrder $record) {
                        try {
                            $fuec = app(FuecGeneratorService::class)->generate($record);
                            Notification::make()
                                ->title('FUEC Emitido con Éxito')
                                ->body("N° Oficial: {$fuec->fuec_number}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al emitir FUEC')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('descargar_fuec')
                    ->label('PDF FUEC')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->visible(fn (ServiceOrder $record) => (bool) $record->fuec)
                    ->url(fn (ServiceOrder $record) => route('tenant.fuec.download', ['fuec_number' => $record->fuec?->fuec_number]))
                    ->openUrlInNewTab(),

                Action::make('solicitar_cancelacion')
                    ->label('Solicitar Cancelación')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('warning')
                    ->visible(fn (ServiceOrder $record) => in_array($record->status, ['Pendiente', 'Asignada', 'En Progreso']))
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo Detallado de la Cancelación')
                            ->placeholder('Explique la razón de la solicitud de cancelación...')
                            ->required(),
                    ])
                    ->action(function (ServiceOrder $record, array $data) {
                        $user = auth()->user()?->name ?? 'Operador';
                        $record->update([
                            'service_notes' => trim(($record->service_notes ?? '')."\n[SOLICITUD CANCELACIÓN {$user} - ".now()->format('Y-m-d H:i').']: '.$data['motivo']),
                        ]);
                        Notification::make()
                            ->title('Solicitud de Cancelación Registrada')
                            ->body('Se ha registrado la solicitud para revisión gerencial.')
                            ->warning()
                            ->send();
                    }),

                Action::make('aprobar_cancelacion')
                    ->label('Aprobar Cancelación (Gerencia)')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobación Gerencial de Cancelación')
                    ->modalDescription('¿Confirma la cancelación definitiva de esta orden de servicio?')
                    ->visible(fn (ServiceOrder $record) => in_array($record->status, ['Pendiente', 'Asignada', 'En Progreso']))
                    ->action(function (ServiceOrder $record) {
                        $user = auth()->user()?->name ?? 'Gerencia';
                        $record->update([
                            'status' => 'Cancelada',
                            'service_notes' => trim(($record->service_notes ?? '')."\n[CANCELACIÓN APROBADA POR {$user} - ".now()->format('Y-m-d H:i').']'),
                        ]);
                        Notification::make()
                            ->title('Orden de Servicio Cancelada')
                            ->body("La orden #{$record->order_number} ha sido cancelada por gerencia.")
                            ->danger()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrders::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'view' => ViewServiceOrder::route('/{record}'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
