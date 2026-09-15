<?php

namespace App\Filament\App\Resources\Clients;

use App\Filament\App\Resources\Clients\Pages\CreateClient;
use App\Filament\App\Resources\Clients\Pages\EditClient;
use App\Filament\App\Resources\Clients\Pages\ListClients;
use App\Filament\App\Resources\Clients\Pages\ViewClient;
use App\Models\Tenant\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|\UnitEnum|null $navigationGroup = 'Comercial y Clientes';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tipo y Datos del Cliente')
                ->schema([
                    Radio::make('type')
                        ->label('Tipo de Cliente')
                        ->options([
                            'Empresa' => 'Empresa / Persona Jurídica',
                            'Persona Natural' => 'Persona Natural',
                        ])
                        ->default('Empresa')
                        ->inline()
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('business_name')
                        ->label('Razón Social')
                        ->visible(fn ($get) => $get('type') === 'Empresa')
                        ->required(fn ($get) => $get('type') === 'Empresa')
                        ->columnSpanFull(),

                    TextInput::make('first_name')
                        ->label('Nombres')
                        ->visible(fn ($get) => $get('type') === 'Persona Natural')
                        ->required(fn ($get) => $get('type') === 'Persona Natural'),

                    TextInput::make('last_name')
                        ->label('Apellidos')
                        ->visible(fn ($get) => $get('type') === 'Persona Natural')
                        ->required(fn ($get) => $get('type') === 'Persona Natural'),

                    TextInput::make('document_number')
                        ->label('NIT / Cédula')
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
                        ->label('Dirección')
                        ->maxLength(255),

                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'Activo' => 'Activo',
                            'Inactivo' => 'Inactivo',
                        ])
                        ->default('Activo')
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Empresa' ? 'info' : 'primary'),
                TextColumn::make('display_name')
                    ->label('Cliente / Razón Social')
                    ->searchable(['business_name', 'first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('document_number')
                    ->label('NIT / CC')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono'),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'Empresa' => 'Empresa',
                        'Persona Natural' => 'Persona Natural',
                    ]),
                SelectFilter::make('status')
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
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
