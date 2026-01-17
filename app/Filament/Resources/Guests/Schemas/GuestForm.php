<?php

namespace App\Filament\Resources\Guests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Evento e Localização')
                    ->schema([
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

                        Select::make('promoter_id')
                            ->label('Promoter Responsável')
                            ->relationship(
                                name: 'promoter', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('role', \App\Enums\UserRole::PROMOTER->value)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Dados do Convidado')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('document')
                            ->label('Documento (CPF/RG)')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Status de Check-in')
                    ->description('Estes campos são atualizados automaticamente durante o evento')
                    ->schema([
                        Toggle::make('is_checked_in')
                            ->label('Já realizou Check-in?')
                            ->default(false),
                        DateTimePicker::make('checked_in_at')
                            ->label('Horário do Check-in')
                            ->native(false),
                        Select::make('checked_in_by')
                            ->label('Validado por')
                            ->relationship('validator', 'name')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }
}
