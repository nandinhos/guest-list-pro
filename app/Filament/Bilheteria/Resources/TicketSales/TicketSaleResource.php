<?php

namespace App\Filament\Bilheteria\Resources\TicketSales;

use App\Filament\Bilheteria\Resources\TicketSales\Pages\CreateTicketSale;
use App\Filament\Bilheteria\Resources\TicketSales\Pages\ListTicketSales;
use App\Filament\Bilheteria\Resources\TicketSales\Schemas\TicketSaleForm;
use App\Filament\Bilheteria\Resources\TicketSales\Tables\TicketSalesTable;
use App\Models\TicketSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketSaleResource extends Resource
{
    protected static ?string $model = TicketSale::class;

    protected static ?string $pluralModelLabel = 'Vendas de Ingressos';

    protected static ?string $modelLabel = 'Venda';

    protected static ?string $navigationLabel = 'Vendas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($selectedEventId = session('selected_event_id')) {
            $query->where('event_id', $selectedEventId);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return TicketSaleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketSalesTable::configure($table);
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
            'index' => ListTicketSales::route('/'),
            'create' => CreateTicketSale::route('/create'),
        ];
    }
}
