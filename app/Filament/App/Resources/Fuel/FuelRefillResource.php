<?php

namespace App\Filament\App\Resources\Fuel;

use App\Filament\App\Resources\Fuel\Pages\CreateFuelRefill;
use App\Filament\App\Resources\Fuel\Pages\EditFuelRefill;
use App\Filament\App\Resources\Fuel\Pages\ListFuelRefills;
use App\Filament\App\Resources\Fuel\Pages\ViewFuelRefill;
use App\Models\Tenant\FuelRefill;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

class FuelRefillResource extends Resource
{
    protected static ?string $model = FuelRefill::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|\UnitEnum|null $navigationGroup = 'Mantenimiento y Combustible';

    protected static ?string $navigationLabel = 'Control de Combustible';

    protected static ?string $modelLabel = 'Recarga de Combustible';

    protected static ?string $pluralModelLabel = 'Recargas de Combustible';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalle del Tanqueo')
                ->description('Registro de combustible, odómetro y rendimiento calculado')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->relationship('vehicle', 'plate')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('driver_id')
                            ->label('Conductor que Recarga')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('service_order_id')
                            ->label('Orden de Servicio (Opcional)')
                            ->relationship('serviceOrder', 'order_number')
                            ->searchable()
                            ->preload(),
                    ]),

                    Grid::make(3)->schema([
                        DatePicker::make('refill_date')
                            ->label('Fecha del Tanqueo')
                            ->default(now()->toDateString())
                            ->required(),

                        TextInput::make('gallons')
                            ->label('Cantidad de Galones')
                            ->numeric()
                            ->step(0.001)
                            ->suffix('gl')
                            ->required(),

                        TextInput::make('total_cost')
                            ->label('Valor Total Pagado ($ COP)')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('odometer_mileage')
                            ->label('Lectura de Odómetro (Km)')
                            ->numeric()
                            ->suffix('km')
                            ->required()
                            ->helperText('Kilometraje exacto al momento de la recarga'),

                        TextInput::make('price_per_gallon')
                            ->label('Precio por Galón')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->helperText('Calculado automáticamente'),

                        TextInput::make('gas_station_name')
                            ->label('Estación de Servicio (EDS)')
                            ->placeholder('Ej: Terpel Autopista Norte / Primax'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('distance_since_last_refill')
                            ->label('Km Recorridos Desde Último Tanqueo')
                            ->numeric()
                            ->suffix('km')
                            ->disabled(),

                        TextInput::make('calculated_performance')
                            ->label('Rendimiento Obtenido')
                            ->numeric()
                            ->suffix('km / galón')
                            ->disabled()
                            ->helperText('Rendimiento medio entre recargas consecutivas'),
                    ]),

                    Grid::make(2)->schema([
                        FileUpload::make('receipt_photo')
                            ->label('Foto del Recibo / Factura EDS')
                            ->directory('fuel_receipts')
                            ->image(),

                        FileUpload::make('odometer_photo')
                            ->label('Foto del Tablero / Odómetro')
                            ->directory('fuel_odometers')
                            ->image(),
                    ]),

                    Textarea::make('notes')
                        ->label('Observaciones Adicionales')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('refill_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vehicle.plate')
                    ->label('Vehículo')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('driver.name')
                    ->label('Conductor')
                    ->limit(20)
                    ->default('N/A'),

                TextColumn::make('gallons')
                    ->label('Galones')
                    ->suffix(' gl')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('Costo')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),

                TextColumn::make('odometer_mileage')
                    ->label('Odómetro')
                    ->numeric(0)
                    ->suffix(' km')
                    ->sortable(),

                TextColumn::make('distance_since_last_refill')
                    ->label('Recorrido')
                    ->suffix(' km')
                    ->default('-')
                    ->sortable(),

                TextColumn::make('calculated_performance')
                    ->label('Rendimiento')
                    ->suffix(' km/gl')
                    ->badge()
                    ->color(function ($state): string {
                        if (! $state) {
                            return 'gray';
                        }
                        $val = (float) $state;
                        if ($val < 15.0) {
                            return 'danger';
                        }
                        if ($val < 25.0) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->default('Inicial'),

                TextColumn::make('gas_station_name')
                    ->label('Estación')
                    ->limit(15)
                    ->default('-'),
            ])
            ->filters([
                SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'plate'),

                SelectFilter::make('driver_id')
                    ->label('Conductor')
                    ->relationship('driver', 'name'),
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
            'index' => ListFuelRefills::route('/'),
            'create' => CreateFuelRefill::route('/create'),
            'view' => ViewFuelRefill::route('/{record}'),
            'edit' => EditFuelRefill::route('/{record}/edit'),
        ];
    }
}
