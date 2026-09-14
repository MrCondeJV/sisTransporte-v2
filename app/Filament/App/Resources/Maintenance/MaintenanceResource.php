<?php

namespace App\Filament\App\Resources\Maintenance;

use App\Filament\App\Resources\Maintenance\Pages\CreateMaintenance;
use App\Filament\App\Resources\Maintenance\Pages\EditMaintenance;
use App\Filament\App\Resources\Maintenance\Pages\ListMaintenances;
use App\Filament\App\Resources\Maintenance\Pages\ViewMaintenance;
use App\Models\Tenant\Maintenance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string | \UnitEnum | null $navigationGroup = 'Mantenimiento y Combustible';

    protected static ?string $navigationLabel = 'Mantenimientos';

    protected static ?string $modelLabel = 'Mantenimiento';

    protected static ?string $pluralModelLabel = 'Mantenimientos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Registro de Mantenimiento')
                ->description('Historial preventivo y correctivo de la flota automotriz')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->relationship('vehicle', 'plate')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('maintenance_type')
                            ->label('Tipo de Mantenimiento')
                            ->options([
                                'Preventivo' => 'Preventivo (Programado / Rutina)',
                                'Correctivo' => 'Correctivo (Reparación / Falla)',
                            ])
                            ->default('Preventivo')
                            ->required(),

                        DatePicker::make('maintenance_date')
                            ->label('Fecha del Servicio')
                            ->default(now()->toDateString())
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('mileage')
                            ->label('Kilometraje al Ingresar')
                            ->numeric()
                            ->required(),

                        TextInput::make('cost')
                            ->label('Costo Total ($ COP)')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required(),

                        TextInput::make('workshop_name')
                            ->label('Taller / Proveedor')
                            ->placeholder('Ej: Taller Central Diesel S.A.S.'),
                    ]),

                    Textarea::make('details')
                        ->label('Trabajos Realizados y Diagnóstico')
                        ->placeholder('Detalle de mano de obra, ajustes, cambio de fluidos o sistemas intervenidos...')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        TagsInput::make('replaced_parts')
                            ->label('Repuestos / Insumos Reemplazados')
                            ->placeholder('Presione Enter para agregar (ej: Aceite 15W40, Filtro de Aire, Pastillas Delanteras)'),

                        FileUpload::make('receipt_file')
                            ->label('Factura / Soporte del Taller')
                            ->directory('maintenances')
                            ->acceptedFileTypes(['application/pdf', 'image/*']),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('maintenance_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('vehicle.plate')
                    ->label('Vehículo')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('maintenance_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Preventivo' => 'info',
                        'Correctivo' => 'warning',
                        default => 'secondary',
                    }),

                TextColumn::make('mileage')
                    ->label('Kilometraje')
                    ->numeric(0)
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('Costo')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),

                TextColumn::make('workshop_name')
                    ->label('Taller')
                    ->limit(20)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('maintenance_type')
                    ->options([
                        'Preventivo' => 'Preventivo',
                        'Correctivo' => 'Correctivo',
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
            'index' => ListMaintenances::route('/'),
            'create' => CreateMaintenance::route('/create'),
            'view' => ViewMaintenance::route('/{record}'),
            'edit' => EditMaintenance::route('/{record}/edit'),
        ];
    }
}
