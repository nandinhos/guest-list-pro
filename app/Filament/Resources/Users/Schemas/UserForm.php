<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Identificação')
                    ->description('Dados básicos do usuário')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Acesso e Permissões')
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        Select::make('role')
                            ->label('Perfil de Acesso')
                            ->options(\App\Enums\UserRole::class)
                            ->default(\App\Enums\UserRole::VALIDATOR)
                            ->required()
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Conta Ativa')
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
