<?php

namespace App\Filament\App\Resources\Employees;

use App\Filament\App\Resources\Employees\Pages\CreateEmployee;
use App\Filament\App\Resources\Employees\Pages\EditEmployee;
use App\Filament\App\Resources\Employees\Pages\ListEmployees;
use App\Filament\App\Resources\Employees\Pages\ViewEmployee;
use App\Models\Tenant\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string | \UnitEnum | null $navigationGroup = 'Personal y Aliados';

    protected static ?string $navigationLabel = 'Conductores y Personal';

    protected static ?string $modelLabel = 'Empleado / Conductor';

    protected static ?string $pluralModelLabel = 'Conductores y Personal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos Personales')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre Completo')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('document_number')
                        ->label('Cédula / Documento')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),
                    TextInput::make('phone')
                        ->label('Teléfono / Móvil')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->label('Dirección de Residencia')
                        ->maxLength(255),
                    Select::make('employee_type')
                        ->label('Tipo de Personal')
                        ->options([
                            'Conductor' => 'Conductor',
                            'Administrativo' => 'Administrativo',
                            'Operativo' => 'Operativo',
                        ])
                        ->default('Conductor')
                        ->required(),
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'Activo' => 'Activo',
                            'Inactivo' => 'Inactivo',
                        ])
                        ->default('Activo')
                        ->required(),
                    Select::make('partner_id')
                        ->label('Aliado Asociado')
                        ->relationship('partner', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    FileUpload::make('photo')
                        ->label('Fotografía')
                        ->image()
                        ->directory('employees/photos')
                        ->visibility('private')
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Licencia de Conducción (Para Conductores)')
                ->schema([
                    TextInput::make('driver_license_number')
                        ->label('Número de Licencia')
                        ->maxLength(50),
                    Select::make('driver_license_category')
                        ->label('Categoría')
                        ->options([
                            'B1' => 'B1 (Automóvil particular)',
                            'B2' => 'B2 (Camión / Bus particular)',
                            'C1' => 'C1 (Automóvil público)',
                            'C2' => 'C2 (Buseta / Camión público)',
                            'C3' => 'C3 (Articulado público)',
                        ]),
                    DatePicker::make('driver_license_expiration')
                        ->label('Fecha de Vencimiento Licencia'),
                ])->columns(3),

            Section::make('Contrato Laboral')
                ->schema([
                    TextInput::make('contract_number')
                        ->label('Número de Contrato'),
                    Select::make('contract_type')
                        ->label('Tipo de Contrato')
                        ->options([
                            'Termino Fijo' => 'Término Fijo',
                            'Termino Indefinido' => 'Término Indefinido',
                            'Prestacion Servicios' => 'Prestación de Servicios',
                            'Otro' => 'Otro',
                        ]),
                    DatePicker::make('contract_start_date')
                        ->label('Fecha Inicio'),
                    DatePicker::make('contract_end_date')
                        ->label('Fecha Fin'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('document_number')
                    ->label('Cédula')
                    ->searchable(),
                TextColumn::make('employee_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Conductor' => 'info',
                        'Administrativo' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('phone')
                    ->label('Teléfono'),
                TextColumn::make('driver_license_category')
                    ->label('Cat. Licencia')
                    ->badge()
                    ->placeholder('N/A'),
                TextColumn::make('driver_license_expiration')
                    ->label('Venc. Licencia')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function (?string $state): string {
                        if (!$state) return 'gray';
                        $date = \Carbon\Carbon::parse($state);
                        if ($date->isPast()) return 'danger';
                        if ($date->diffInDays(now()) <= 30) return 'warning';
                        return 'success';
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('employee_type')
                    ->label('Tipo')
                    ->options([
                        'Conductor' => 'Conductor',
                        'Administrativo' => 'Administrativo',
                        'Operativo' => 'Operativo',
                    ]),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
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
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
