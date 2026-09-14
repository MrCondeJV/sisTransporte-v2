<?php

namespace App\Filament\App\Resources\Checklists;

use App\Filament\App\Resources\Checklists\Pages\CreatePreoperationalChecklist;
use App\Filament\App\Resources\Checklists\Pages\EditPreoperationalChecklist;
use App\Filament\App\Resources\Checklists\Pages\ListPreoperationalChecklists;
use App\Filament\App\Resources\Checklists\Pages\ViewPreoperationalChecklist;
use App\Models\Tenant\PreoperationalChecklist;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PreoperationalChecklistResource extends Resource
{
    protected static ?string $model = PreoperationalChecklist::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string | \UnitEnum | null $navigationGroup = 'Operaciones y Servicios';

    protected static ?string $navigationLabel = 'Checklist Preoperacional';

    protected static ?string $modelLabel = 'Checklist Preoperacional';

    protected static ?string $pluralModelLabel = 'Checklists Preoperacionales';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos Generales de Inspección')
                ->description('Vehículo, conductor y kilometraje al momento de la revisión diaria')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->relationship('vehicle', 'plate')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('driver_id')
                            ->label('Conductor que Inspecciona')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('service_order_id')
                            ->label('Orden de Servicio (Opcional)')
                            ->relationship('serviceOrder', 'order_number')
                            ->searchable()
                            ->preload(),
                    ]),

                    Grid::make(3)->schema([
                        DatePicker::make('date')
                            ->label('Fecha')
                            ->default(now()->toDateString())
                            ->required(),

                        TimePicker::make('time')
                            ->label('Hora')
                            ->default(now()->format('H:i'))
                            ->required(),

                        TextInput::make('mileage')
                            ->label('Kilometraje Actual')
                            ->numeric()
                            ->required(),
                    ]),
                ]),

            Section::make('1. Niveles de Fluidos')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('fluid_levels.engine_oil')->label('Nivel Aceite de Motor')->default(true),
                        Toggle::make('fluid_levels.brake_fluid')->label('Líquido de Frenos')->default(true),
                        Toggle::make('fluid_levels.coolant')->label('Líquido Refrigerante')->default(true),
                        Toggle::make('fluid_levels.power_steering')->label('Dirección Hidráulica')->default(true),
                        Toggle::make('fluid_levels.windshield_washer')->label('Agua Limpiabrisas')->default(true),
                        Toggle::make('fluid_levels.no_leaks')->label('Sin Fugas Visibles')->default(true),
                    ]),
                ]),

            Section::make('2. Luces y Sistema Eléctrico')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('lights_and_electrical.high_beams')->label('Luces Altas')->default(true),
                        Toggle::make('lights_and_electrical.low_beams')->label('Luces Bajas')->default(true),
                        Toggle::make('lights_and_electrical.turn_signals')->label('Direccionales y Estacionarias')->default(true),
                        Toggle::make('lights_and_electrical.brake_lights')->label('Luces de Freno')->default(true),
                        Toggle::make('lights_and_electrical.reverse_alarm')->label('Luz y Alarma de Reversa')->default(true),
                        Toggle::make('lights_and_electrical.horn')->label('Bocina / Pito')->default(true),
                    ]),
                ]),

            Section::make('3. Llantas y Frenos')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('tires_and_brakes.tread_depth')->label('Labrado y Desgaste Óptimo')->default(true),
                        Toggle::make('tires_and_brakes.tire_pressure')->label('Presión Adecuada')->default(true),
                        Toggle::make('tires_and_brakes.spare_tire')->label('Llanta de Repuesto en Buen Estado')->default(true),
                        Toggle::make('tires_and_brakes.service_brake')->label('Freno de Servicio (Pedal)')->default(true),
                        Toggle::make('tires_and_brakes.parking_brake')->label('Freno de Emergencia / Mano')->default(true),
                    ]),
                ]),

            Section::make('4. Equipo de Carretera y Prevención')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('safety_kit.first_aid_kit')->label('Botiquín Completo')->default(true),
                        Toggle::make('safety_kit.fire_extinguisher')->label('Extintor Vigente (Carga ok)')->default(true),
                        Toggle::make('safety_kit.road_cones')->label('Conos / Triángulos')->default(true),
                        Toggle::make('safety_kit.jack_and_wrench')->label('Gato y Cruceta')->default(true),
                        Toggle::make('safety_kit.flashlight')->label('Linterna con Baterías')->default(true),
                        Toggle::make('safety_kit.wheel_chocks')->label('Tacos de Bloqueo (2)')->default(true),
                    ]),
                ]),

            Section::make('5. Cabina y Elementos de Seguridad Pasiva')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('cabin_and_belts.seatbelts')->label('Cinturones en Todos los Asientos')->default(true),
                        Toggle::make('cabin_and_belts.mirrors')->label('Espejos Retrovisores')->default(true),
                        Toggle::make('cabin_and_belts.windshield')->label('Panorámico sin Fisuras')->default(true),
                        Toggle::make('cabin_and_belts.wipers')->label('Plumillas Limpiaparabrisas')->default(true),
                        Toggle::make('cabin_and_belts.doors_and_locks')->label('Puertas y Mecanismos de Cierre')->default(true),
                    ]),
                ]),

            Section::make('Dictamen Final y Firma')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_approved')
                            ->label('¿Vehículo Aprobado para Operar?')
                            ->helperText('Si algún ítem crítico falla, no debe aprobarse para rodamiento')
                            ->default(true),

                        TextInput::make('driver_signature')
                            ->label('Firma / Identificador del Conductor')
                            ->placeholder('Nombre completo o Hash de verificación digital')
                            ->required(),
                    ]),

                    Textarea::make('observations')
                        ->label('Observaciones Técnicas o Anotaciones de Desgaste')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('time')
                    ->label('Hora'),

                TextColumn::make('vehicle.plate')
                    ->label('Vehículo')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('driver.name')
                    ->label('Conductor')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('mileage')
                    ->label('Kilometraje')
                    ->numeric(0)
                    ->sortable(),

                IconColumn::make('is_approved')
                    ->label('Aprobado')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('serviceOrder.order_number')
                    ->label('Orden de Servicio')
                    ->default('N/A')
                    ->limit(15),
            ])
            ->filters([
                SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'plate'),

                SelectFilter::make('driver_id')
                    ->label('Conductor')
                    ->relationship('driver', 'name'),

                TernaryFilter::make('is_approved')
                    ->label('Estado de Aprobación'),
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
            'index' => ListPreoperationalChecklists::route('/'),
            'create' => CreatePreoperationalChecklist::route('/create'),
            'view' => ViewPreoperationalChecklist::route('/{record}'),
            'edit' => EditPreoperationalChecklist::route('/{record}/edit'),
        ];
    }
}
