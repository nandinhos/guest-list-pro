<?php

namespace App\Filament\Resources\TicketType\Schemas;

use App\Models\Sector;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TicketTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais do tipo de ingresso')
                    ->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set) => $set('sector_prices', [])),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(500),

                        TextInput::make('price')
                            ->label('Preço Padrão')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Ativo'),
                    ])->columns(2),

                Section::make('Preços por Setor')
                    ->description('Configure preços específicos por setor (opcional)')
                    ->schema([
                        Repeater::make('sector_prices')
                            ->label('')
                            ->relationship('sectorPrices')
                            ->schema([
                                Select::make('sector_id')
                                    ->label('Setor')
                                    ->options(function (Get $get) {
                                        $eventId = $get('../../event_id');
                                        if (! $eventId) {
                                            return [];
                                        }

                                        return Sector::where('event_id', $eventId)
                                            ->pluck('name', 'id');
                                    })
                                    ->disabled(fn (Get $get) => ! $get('../../event_id')),
                                TextInput::make('price')
                                    ->label('Preço Customizado')
                                    ->numeric()
                                    ->prefix('R$'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
