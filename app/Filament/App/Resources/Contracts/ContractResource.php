<?php

namespace App\Filament\App\Resources\Contracts;

use App\Filament\App\Resources\Contracts\Pages\CreateContract;
use App\Filament\App\Resources\Contracts\Pages\EditContract;
use App\Filament\App\Resources\Contracts\Pages\ListContracts;
use App\Filament\App\Resources\Contracts\Pages\ViewContract;
use App\Models\Tenant\Contract;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Comercial y Clientes';

    protected static ?string $navigationLabel = 'Contratos de Transporte';

    protected static ?string $modelLabel = 'Contrato';

    protected static ?string $pluralModelLabel = 'Contratos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del Contrato')
                ->schema([
                    Select::make('client_id')
                        ->label('Cliente Contratante')
                        ->relationship('client', 'business_name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('contract_number')
                        ->label('Número de Contrato')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Select::make('contract_type')
                        ->label('Tipo de Servicio / Objeto')
                        ->options([
                            'Empresarial' => 'Transporte Empresarial',
                            'Turismo' => 'Transporte de Turismo',
                            'Escolar' => 'Transporte Escolar',
                            'Salud' => 'Transporte de Usuarios de Salud',
                            'Grupo Especifico' => 'Grupo Específico de Usuarios',
                        ])
                        ->default('Empresarial')
                        ->required(),
                    TextInput::make('value')
                        ->label('Valor del Contrato ($ COP)')
                        ->numeric()
                        ->prefix('$'),
                    DatePicker::make('start_date')
                        ->label('Fecha de Inicio')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Fecha de Terminación')
                        ->required(),
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'Vigente' => 'Vigente',
                            'Vencido' => 'Vencido',
                            'Cancelado' => 'Cancelado',
                        ])
                        ->default('Vigente')
                        ->required(),
                    Textarea::make('contract_object')
                        ->label('Objeto Detallado del Contrato')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Nro Contrato')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('client.display_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function (?string $state): string {
                        if (! $state) {
                            return 'gray';
                        }
                        $date = Carbon::parse($state);
                        if ($date->isPast()) {
                            return 'danger';
                        }
                        if ($date->diffInDays(now()) <= 30) {
                            return 'warning';
                        }

                        return 'success';
                    }),
                TextColumn::make('value')
                    ->label('Valor')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Vigente' => 'success',
                        'Vencido' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('contract_type')
                    ->options([
                        'Empresarial' => 'Empresarial',
                        'Turismo' => 'Turismo',
                        'Escolar' => 'Escolar',
                        'Salud' => 'Salud',
                        'Grupo Especifico' => 'Grupo Específico',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'Vigente' => 'Vigente',
                        'Vencido' => 'Vencido',
                        'Cancelado' => 'Cancelado',
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
            'index' => ListContracts::route('/'),
            'create' => CreateContract::route('/create'),
            'view' => ViewContract::route('/{record}'),
            'edit' => EditContract::route('/{record}/edit'),
        ];
    }
}
