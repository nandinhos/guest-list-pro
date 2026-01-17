<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informações Básicas')
                    ->description('Dados principais do evento')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Evento')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(EventStatus::class)
                            ->default('draft')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Banner')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('banner_path')
                            ->label('Banner do Evento')
                            ->image()
                            ->directory('event-banners')
                            ->visibility('public'),
                    ]),

                \Filament\Schemas\Components\Section::make('Data e Horário')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Data')
                            ->required()
                            ->native(false),
                        TimePicker::make('start_time')
                            ->label('Início')
                            ->required()
                            ->native(false),
                        TimePicker::make('end_time')
                            ->label('Término')
                            ->required()
                            ->native(false),
                    ])->columns(3),
            ]);
    }
}
