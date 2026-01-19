<?php

namespace App\Filament\Resources\PromoterPermissions\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromoterPermissionForm
{
    /**
     * Roles que podem ser atribuídas a eventos.
     * Exclui ADMIN que tem acesso total.
     */
    private static array $assignableRoles = [
        UserRole::PROMOTER,
        UserRole::VALIDATOR,
        UserRole::BILHETERIA,
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Atribuição')
                    ->description('Vincular usuário ao evento com uma função específica')
                    ->schema([
                        Select::make('role')
                            ->label('Função')
                            ->options(
                                collect(self::$assignableRoles)
                                    ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->getLabel()])
                                    ->toArray()
                            )
                            ->required()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(fn ($set) => $set('user_id', null)),

                        Select::make('user_id')
                            ->label('Usuário')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, Get $get) => $query->where('role', $get('role'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(fn (Get $get): bool => ! $get('role')),

                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])->columns(3),

                Section::make('Configurações de Promoter')
                    ->description('Setor e limite de convidados (apenas para promoters)')
                    ->schema([
                        Select::make('sector_id')
                            ->label('Setor')
                            ->relationship(
                                name: 'sector',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, Get $get) => $query->where('event_id', $get('event_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('role') === UserRole::PROMOTER->value)
                            ->native(false)
                            ->disabled(fn (Get $get): bool => ! $get('event_id')),

                        TextInput::make('guest_limit')
                            ->label('Limite de Convidados')
                            ->required(fn (Get $get): bool => $get('role') === UserRole::PROMOTER->value)
                            ->numeric()
                            ->minValue(1)
                            ->default(100),
                    ])->columns(2)
                    ->visible(fn (Get $get): bool => $get('role') === UserRole::PROMOTER->value),

                Section::make('Período de Atuação')
                    ->description('Opcional. Se não definido, a permissão vale durante todo o evento')
                    ->schema([
                        DateTimePicker::make('start_time')
                            ->label('Início')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false),

                        DateTimePicker::make('end_time')
                            ->label('Fim')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->after('start_time'),
                    ])->columns(2),
            ]);
    }
}
