<?php

namespace App\Filament\Promoter\Resources\Guests\Schemas;

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
                \Filament\Schemas\Components\Section::make('Evento e Setor')
                    ->description('Selecione onde o convidado será alocado')
                    ->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->options(fn () => 
                                \App\Models\Event::whereIn('id', \App\Models\PromoterPermission::where('user_id', auth()->id())->pluck('event_id'))
                                    ->pluck('name', 'id')
                            )
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        
                        Select::make('sector_id')
                            ->label('Setor')
                            ->options(fn (\Filament\Schemas\Components\Utilities\Get $get) => 
                                \App\Models\Sector::whereIn('id', 
                                    \App\Models\PromoterPermission::where('user_id', auth()->id())
                                        ->where('event_id', $get('event_id'))
                                        ->pluck('sector_id')
                                )->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get) => ! $get('event_id')),

                        \Filament\Forms\Components\Placeholder::make('remaining')
                            ->label('Convites Restantes')
                            ->content(fn (\Filament\Schemas\Components\Utilities\Get $get) => 
                                (new \App\Services\GuestService())->canRegisterGuest(auth()->user(), (int)$get('event_id'), (int)$get('sector_id'))['remaining'] ?? 'Selecione o setor'
                            )
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('sector_id')),
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
            ]);
    }
}
