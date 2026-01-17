<?php

namespace App\Filament\Resources\Sectors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SectorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detalhes do Setor')
                    ->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        TextInput::make('name')
                            ->label('Nome do Setor')
                            ->placeholder('Ex: VIP, Pista...')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('capacity')
                            ->label('Capacidade Máxima')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Opcional'),
                    ]),
            ]);
    }
}
