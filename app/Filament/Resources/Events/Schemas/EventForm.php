<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
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

                Section::make('Banner')
                    ->description('Imagem de capa do evento')
                    ->schema([
                        Toggle::make('use_external_banner')
                            ->label('Usar link externo')
                            ->helperText('Ative para usar uma URL externa ao invés de fazer upload')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, $record): void {
                                $set('use_external_banner', filled($record?->banner_url));
                            }),

                        FileUpload::make('banner_path')
                            ->label('Upload do Banner')
                            ->image()
                            ->directory('event-banners')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:10')
                            ->imageResizeTargetWidth('1280')
                            ->imageResizeTargetHeight('800')
                            ->visible(fn (Get $get): bool => ! $get('use_external_banner')),

                        TextInput::make('banner_url')
                            ->label('URL do Banner')
                            ->url()
                            ->placeholder('https://exemplo.com/imagem.jpg')
                            ->helperText('Cole a URL de uma imagem externa')
                            ->visible(fn (Get $get): bool => (bool) $get('use_external_banner')),
                    ]),

                Section::make('Local')
                    ->schema([
                        TextInput::make('location')
                            ->label('Local do Evento')
                            ->placeholder('Ex: Arena Show, São Paulo - SP')
                            ->maxLength(255),
                    ]),

                Section::make('Data e Horário')
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
