<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Empresa')
                    ->description('Datos principales para la creación de la empresa y su base de datos')
                    ->schema([
                        TextInput::make('id')
                            ->label('Identificador Único (Slug)')
                            ->helperText('Usado para el nombre de la base de datos y subdominio (ej: empresa1, transvalle)')
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit'),
                        TextInput::make('name')
                            ->label('Razón Social / Nombre Comercial')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nit')
                            ->label('NIT / Identificación Tributaria')
                            ->maxLength(30),
                        TextInput::make('subdomain')
                            ->label('Subdominio asignado')
                            ->helperText('Ej: empresa1 (accesible en empresa1.localhost)')
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono de Contacto')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Empresa Activa')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
