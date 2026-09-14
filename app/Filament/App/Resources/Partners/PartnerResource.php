<?php

namespace App\Filament\App\Resources\Partners;

use App\Filament\App\Resources\Partners\Pages\CreatePartner;
use App\Filament\App\Resources\Partners\Pages\EditPartner;
use App\Filament\App\Resources\Partners\Pages\ListPartners;
use App\Filament\App\Resources\Partners\Pages\ViewPartner;
use App\Models\Tenant\Partner;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string | \UnitEnum | null $navigationGroup = 'Personal y Aliados';

    protected static ?string $navigationLabel = 'Aliados Estratégicos';

    protected static ?string $modelLabel = 'Aliado';

    protected static ?string $pluralModelLabel = 'Aliados';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del Aliado')
                ->schema([
                    TextInput::make('name')
                        ->label('Razón Social / Nombre del Aliado')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('nit')
                        ->label('NIT / Identificación')
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),
                    TextInput::make('contact_person')
                        ->label('Persona de Contacto')
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Teléfono')
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
                TextColumn::make('name')
                    ->label('Aliado')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable(),
                TextColumn::make('contact_person')
                    ->label('Contacto')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono'),
                TextColumn::make('vehicles_count')
                    ->label('Vehículos Vinculados')
                    ->counts('vehicles')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
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
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'view' => ViewPartner::route('/{record}'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
