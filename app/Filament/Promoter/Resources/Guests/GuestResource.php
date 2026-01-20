<?php

namespace App\Filament\Promoter\Resources\Guests;

use App\Filament\Promoter\Resources\Guests\Pages\CreateGuest;
use App\Filament\Promoter\Resources\Guests\Pages\EditGuest;
use App\Filament\Promoter\Resources\Guests\Pages\ListGuests;
use App\Filament\Promoter\Resources\Guests\Schemas\GuestForm;
use App\Filament\Promoter\Resources\Guests\Tables\GuestsTable;
use App\Models\Guest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if ($selectedEventId = session('selected_event_id')) {
            $query->where('event_id', $selectedEventId);
        }

        return $query->where('promoter_id', auth()->id());
    }

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
            'create' => CreateGuest::route('/create'),
            'edit' => EditGuest::route('/{record}/edit'),
            'import' => Pages\ImportGuests::route('/import'),
        ];
    }
}
