<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Tables;

use App\Enums\PaymentMethod;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('buyer_name')
                    ->label('Comprador')
                    ->description(fn ($record) => $record->buyer_document ?? '-')
                    ->searchable(['buyer_name', 'buyer_document'])
                    ->sortable(),

                TextColumn::make('guest.name')
                    ->label('Convidado Gerado')
                    ->description(fn ($record) => $record->guest?->sector?->name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PaymentMethod::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state) => PaymentMethod::tryFrom($state)?->getColor() ?? 'gray'),

                TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sector')
                    ->label('Setor')
                    ->options(fn () => \App\Models\Sector::query()
                        ->where('event_id', session('selected_event_id'))
                        ->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $sectorId) => $q->whereHas('guest', fn ($g) => $g->where('sector_id', $sectorId))
                    ))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('Pagamento')
                    ->options(PaymentMethod::class),

                SelectFilter::make('sold_by')
                    ->label('Vendedor')
                    ->relationship('seller', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Data')
                    ->form([
                        DatePicker::make('from')
                            ->label('De'),
                        DatePicker::make('until')
                            ->label('Até'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->defaultSort('created_at', 'desc');
    }
}
