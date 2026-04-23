<?php

namespace App\Filament\Validator\Resources\Guests\Schemas;

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
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),

                Select::make('sector_id')
                    ->relationship('sector', 'name')
                    ->required(),

                Select::make('promoter_id')
                    ->relationship('promoter', 'name')
                    ->required(),

                TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nome como no documento'),

                TextInput::make('document')
                    ->label('Documento')
                    ->required()
                    ->maxLength(20),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255)
                    ->placeholder('email@exemplo.com (opcional)'),

                Toggle::make('is_checked_in')
                    ->label('Já realizou Check-in?')
                    ->disabled(),

                DateTimePicker::make('checked_in_at')
                    ->label('Horário do Check-in')
                    ->disabled(),

                TextInput::make('checked_in_by')
                    ->label('Validado por')
                    ->numeric()
                    ->disabled(),
            ]);
    }
}
