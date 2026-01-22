<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Tables;

use App\Enums\PaymentMethod;
use Filament\Forms\Components\DateTimePicker;
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
                \Filament\Tables\Columns\ViewColumn::make('mobile_card')
                    ->label('VENDA')
                    ->view('filament.bilheteria.resources.ticket-sales.mobile_card')
                    ->hiddenFrom('md'),

                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('buyer_name')
                    ->label('Comprador')
                    ->description(fn ($record) => $record->buyer_document ?? '-')
                    ->searchable(['buyer_name', 'buyer_document'])
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('guest.name')
                    ->label('Convidado Gerado')
                    ->description(fn ($record) => $record->guest?->sector?->name)
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PaymentMethod::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state) => PaymentMethod::tryFrom($state)?->getColor() ?? 'gray')
                    ->visibleFrom('md'),

                TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->visibleFrom('md'),
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
                    ->label('Período')
                    ->form([
                        DateTimePicker::make('from')
                            ->label('Início'),
                        DateTimePicker::make('until')
                            ->label('Até'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $dateTime) => $q->where('created_at', '>=', $dateTime))
                        ->when($data['until'], fn ($q, $dateTime) => $q->where('created_at', '<=', $dateTime))),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->defaultSort('created_at', 'desc');
    }
}
