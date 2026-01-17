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
                    ->required(),
                TextInput::make('document')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Toggle::make('is_checked_in')
                    ->required(),
                DateTimePicker::make('checked_in_at'),
                TextInput::make('checked_in_by')
                    ->numeric(),
            ]);
    }
}
