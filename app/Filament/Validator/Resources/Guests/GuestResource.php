<?php

namespace App\Filament\Validator\Resources\Guests;

use App\Filament\Validator\Resources\Guests\Pages\ListGuests;
use App\Filament\Validator\Resources\Guests\Schemas\GuestForm;
use App\Filament\Validator\Resources\Guests\Tables\GuestsTable;
use App\Models\Guest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $pluralModelLabel = 'Check-in de Convidados';

    protected static ?string $modelLabel = 'Check-in';

    protected static ?string $navigationLabel = 'Check-in';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    public static function form(Schema $schema): Schema
    {
        return GuestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuests::route('/'),
        ];
    }
}
