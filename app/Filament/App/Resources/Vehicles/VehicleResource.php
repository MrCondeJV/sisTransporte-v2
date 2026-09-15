<?php

namespace App\Filament\App\Resources\Vehicles;

use App\Filament\App\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\App\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\App\Resources\Vehicles\Pages\ListVehicles;
use App\Filament\App\Resources\Vehicles\Pages\ViewVehicle;
use App\Models\Tenant\Vehicle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|\UnitEnum|null $navigationGroup = 'Flota y Equipos';

    protected static ?string $navigationLabel = 'Vehículos';

    protected static ?string $modelLabel = 'Vehículo';

    protected static ?string $pluralModelLabel = 'Vehículos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del Vehículo')
                ->description('Identificación y características técnicas')
                ->schema([
                    TextInput::make('plate')
                        ->label('Placa')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('ABC123'),
                    TextInput::make('internal_number')
                        ->label('Número Interno')
                        ->maxLength(20),
                    TextInput::make('brand')
                        ->label('Marca')
                        ->required()
                        ->placeholder('Mercedes-Benz, Chevrolet...'),
                    TextInput::make('line')
                        ->label('Línea / Referencia')
                        ->placeholder('Sprinter, NKR, D-Max...'),
                    TextInput::make('model_year')
                        ->label('Año / Modelo')
                        ->numeric()
                        ->required()
                        ->default(date('Y')),
                    TextInput::make('color')
                        ->label('Color'),
                    Select::make('vehicle_type')
                        ->label('Tipo de Vehículo')
                        ->options([
                            'Automovil' => 'Automóvil',
                            'Camioneta' => 'Camioneta',
                            'Van' => 'Van',
                            'Microbus' => 'Microbús',
                            'Buseta' => 'Buseta',
                            'Bus' => 'Bus',
                        ])
                        ->default('Van')
                        ->required(),
                    TextInput::make('passenger_capacity')
                        ->label('Capacidad Pasajeros')
                        ->numeric()
                        ->default(4)
                        ->required(),
                    TextInput::make('current_mileage')
                        ->label('Kilometraje Actual (km)')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Select::make('status')
                        ->label('Estado Operativo')
                        ->options([
                            'Activo' => 'Activo',
                            'Mantenimiento' => 'En Mantenimiento',
                            'Inactivo' => 'Inactivo',
                        ])
                        ->default('Activo')
                        ->required(),
                    Select::make('partner_id')
                        ->label('Aliado / Propietario')
                        ->relationship('partner', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('default_driver_id')
                        ->label('Conductor Asignado')
                        ->relationship('defaultDriver', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ])->columns(3),

            Section::make('Documentación y Vigencias (Regulación Colombia)')
                ->description('Control de vencimientos para habilitación en servicio')
                ->schema([
                    TextInput::make('soat_number')->label('Número SOAT'),
                    DatePicker::make('soat_expiration')->label('Vencimiento SOAT')->required(),
                    TextInput::make('technomechanical_number')->label('Número Tecnomecánica'),
                    DatePicker::make('technomechanical_expiration')->label('Vencimiento Tecnomecánica')->required(),
                    TextInput::make('contractual_policy_number')->label('Póliza Contractual'),
                    DatePicker::make('contractual_policy_expiration')->label('Vencimiento Póliza Contractual')->required(),
                    TextInput::make('extra_contractual_policy_number')->label('Póliza Extracontractual'),
                    DatePicker::make('extra_contractual_policy_expiration')->label('Vencimiento Póliza Extracontractual')->required(),
                    TextInput::make('operation_card_number')->label('Tarjeta de Operación'),
                    DatePicker::make('operation_card_expiration')->label('Vencimiento Tarjeta Operación')->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate')
                    ->label('Placa')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('internal_number')
                    ->label('Nro Interno')
                    ->sortable(),
                TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable(),
                TextColumn::make('line')
                    ->label('Línea'),
                TextColumn::make('model_year')
                    ->label('Año')
                    ->sortable(),
                TextColumn::make('current_mileage')
                    ->label('Km Actual')
                    ->numeric(0)
                    ->suffix(' km')
                    ->sortable(),
                TextColumn::make('document_status')
                    ->label('Documentos')
                    ->badge()
                    ->color(fn (Vehicle $record): string => $record->document_status_color),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Activo' => 'success',
                        'Mantenimiento' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('partner.name')
                    ->label('Aliado')
                    ->placeholder('Propio')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Mantenimiento' => 'En Mantenimiento',
                        'Inactivo' => 'Inactivo',
                    ]),
                SelectFilter::make('vehicle_type')
                    ->label('Tipo de Vehículo')
                    ->options([
                        'Automovil' => 'Automóvil',
                        'Camioneta' => 'Camioneta',
                        'Van' => 'Van',
                        'Microbus' => 'Microbús',
                        'Buseta' => 'Buseta',
                        'Bus' => 'Bus',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicles::route('/'),
            'create' => CreateVehicle::route('/create'),
            'view' => ViewVehicle::route('/{record}'),
            'edit' => EditVehicle::route('/{record}/edit'),
        ];
    }
}
