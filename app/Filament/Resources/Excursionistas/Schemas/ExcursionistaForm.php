<?php

namespace App\Filament\Resources\Excursionistas\Schemas;

use App\Models\EventAssignment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExcursionistaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Excursionista')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->rules(['min:8']),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ]),

                Section::make('Eventos Vinculados')
                    ->description('Eventos que este excursionista pode gerenciar')
                    ->schema([
                        Select::make('eventAssignments')
                            ->label('Eventos')
                            ->relationship('eventAssignments', 'event_id')
                            ->getOptionLabelFromRecordUsing(fn (EventAssignment $record) => $record->event?->name ?? 'Evento')
                            ->multiple()
                            ->preload()
                            ->native(false),
                    ]),
            ]);
    }
}
