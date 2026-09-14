<?php

namespace App\Filament\App\Resources\Incidents;

use App\Filament\App\Resources\Incidents\Pages\CreateServiceIncident;
use App\Filament\App\Resources\Incidents\Pages\EditServiceIncident;
use App\Filament\App\Resources\Incidents\Pages\ListServiceIncidents;
use App\Filament\App\Resources\Incidents\Pages\ViewServiceIncident;
use App\Models\Tenant\ServiceIncident;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceIncidentResource extends Resource
{
    protected static ?string $model = ServiceIncident::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string | \UnitEnum | null $navigationGroup = 'Operaciones y Servicios';

    protected static ?string $navigationLabel = 'Novedades e Incidentes';

    protected static ?string $modelLabel = 'Novedad / Incidente';

    protected static ?string $pluralModelLabel = 'Novedades e Incidentes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalle del Incidente / Novedad')
                ->description('Registro de fallas mecánicas, eventos viales, demoras o accidentes en operación')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('incident_type')
                            ->label('Tipo de Incidente')
                            ->options([
                                'Mecanica' => 'Falla Mecánica',
                                'Trafico' => 'Retraso por Tráfico / Vía Cerrada',
                                'Accidente' => 'Accidente / Choque',
                                'Pasajero' => 'Novedad con Pasajero',
                                'Clima' => 'Condición Climática Adversa',
                                'Otro' => 'Otro Evento',
                            ])
                            ->required(),

                        Select::make('vehicle_id')
                            ->label('Vehículo Involucrado')
                            ->relationship('vehicle', 'plate')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('driver_id')
                            ->label('Conductor que Reporta')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('service_order_id')
                            ->label('Orden de Servicio Vinculada')
                            ->relationship('serviceOrder', 'order_number')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional si el evento ocurrió fuera de un servicio activo'),

                        DateTimePicker::make('reported_at')
                            ->label('Fecha y Hora del Reporte')
                            ->default(now())
                            ->required(),
                    ]),

                    Textarea::make('description')
                        ->label('Descripción Detallada de los Hechos')
                        ->placeholder('Describa con precisión lo ocurrido, lugar exacto, estado del vehículo y acciones inmediatas tomadas...')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Estado del Caso')
                            ->options([
                                'Abierta' => 'Abierta',
                                'En Revision' => 'En Revisión',
                                'Resuelta' => 'Resuelta',
                            ])
                            ->default('Abierta')
                            ->required(),

                        FileUpload::make('photos')
                            ->label('Evidencia Fotográfica / Soportes')
                            ->multiple()
                            ->directory('incidents')
                            ->image()
                            ->maxFiles(5),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reported_at')
                    ->label('Fecha Reporte')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('incident_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Accidente' => 'danger',
                        'Mecanica' => 'warning',
                        'Trafico' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('vehicle.plate')
                    ->label('Vehículo')
                    ->badge(),

                TextColumn::make('driver.name')
                    ->label('Conductor')
                    ->limit(20),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Abierta' => 'danger',
                        'En Revision' => 'warning',
                        'Resuelta' => 'success',
                        default => 'secondary',
                    }),

                TextColumn::make('serviceOrder.order_number')
                    ->label('Orden N°')
                    ->default('N/A'),
            ])
            ->filters([
                SelectFilter::make('incident_type')
                    ->label('Tipo de Incidente')
                    ->options([
                        'Mecanica' => 'Falla Mecánica',
                        'Trafico' => 'Retraso por Tráfico',
                        'Accidente' => 'Accidente / Choque',
                        'Pasajero' => 'Novedad Pasajero',
                        'Clima' => 'Clima',
                        'Otro' => 'Otro',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Abierta' => 'Abierta',
                        'En Revision' => 'En Revisión',
                        'Resuelta' => 'Resuelta',
                    ]),

                SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'plate'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => ListServiceIncidents::route('/'),
            'create' => CreateServiceIncident::route('/create'),
            'view' => ViewServiceIncident::route('/{record}'),
            'edit' => EditServiceIncident::route('/{record}/edit'),
        ];
    }
}
