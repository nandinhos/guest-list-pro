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
            ->modifyQueryUsing(fn ($query) => $query->with(['guest', 'seller', 'event', 'ticketType']))
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

                TextColumn::make('ticketType.name')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($record) => format_money($record->value))
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
                    ->formatStateUsing(fn ($record) => format_datetime($record->created_at))
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('ticket_type')
                    ->label('Tipo de Ingresso')
                    ->options(fn () => \App\Models\TicketType::query()
                        ->where('event_id', session('selected_event_id'))
                        ->where('is_active', true)
                        ->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $typeId) => $q->where('ticket_type_id', $typeId)
                    ))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('sector')
                    ->label('Setor')
                    ->options(fn () => \App\Models\Sector::query()
                        ->where('event_id', session('selected_event_id'))
                        ->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $sectorId) => $q->where('sector_id', $sectorId)
                    ))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('Pagamento')
                    ->options(PaymentMethod::class),

                SelectFilter::make('sold_by')
                    ->label('Vendedor')
                    ->relationship('seller', 'name', fn ($query) => $query->where('role', \App\Enums\UserRole::BILHETERIA))
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
            ->filtersFormColumns(5)
            ->defaultSort('created_at', 'desc');
    }
}
