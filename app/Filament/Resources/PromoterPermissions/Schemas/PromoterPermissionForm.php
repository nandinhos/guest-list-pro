<?php

namespace App\Filament\Resources\PromoterPermissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class PromoterPermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Autorização')
                    ->description('Vincular promoter ao evento e setor')
                    ->schema([
                        Select::make('user_id')
                            ->label('Promoter')
                            ->relationship(
                                name: 'user', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('role', \App\Enums\UserRole::PROMOTER->value)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('sector_id')
                            ->label('Setor')
                            ->relationship(
                                name: 'sector', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, \Filament\Schemas\Components\Utilities\Get $get) => 
                                    $query->where('event_id', $get('event_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get) => ! $get('event_id')),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Limites e Regras')
                    ->schema([
                        TextInput::make('guest_limit')
                            ->label('Limite de Convidados')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(100),
                        TimePicker::make('start_time')
                            ->label('Início Permitido')
                            ->native(false),
                        TimePicker::make('end_time')
                            ->label('Fim Permitido')
                            ->native(false),
                    ])->columns(3),
            ]);
    }
}
