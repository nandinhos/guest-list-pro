<?php

namespace App\Filament\Resources\TicketType;

use App\Filament\Resources\TicketType\Pages\CreateTicketType;
use App\Filament\Resources\TicketType\Pages\EditTicketType;
use App\Filament\Resources\TicketType\Pages\ListTicketTypes;
use App\Filament\Resources\TicketType\Schemas\TicketTypeForm;
use App\Filament\Resources\TicketType\Tables\TicketTypesTable;
use App\Models\TicketType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketTypeResource extends Resource
{
    protected static ?string $model = TicketType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $modelLabel = 'Tipo de Ingresso';

    protected static ?string $pluralModelLabel = 'Tipos de Ingresso';

    public static function form(Schema $schema): Schema
    {
        return TicketTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTicketTypes::route('/'),
            'create' => CreateTicketType::route('/create'),
            'edit' => EditTicketType::route('/{record}/edit'),
        ];
    }
}
