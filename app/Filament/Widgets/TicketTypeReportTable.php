<?php

namespace App\Filament\Widgets;

use App\Models\TicketType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketTypeReportTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $eventId = session('selected_event_id');

        return TicketType::query()
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->withCount([
                'ticketSales as total_sales',
                'ticketSales as today_sales' => fn ($q) => $q->whereDate('created_at', today()),
            ])
            ->withSum('ticketSales as total_revenue', 'value')
            ->withSum([
                'ticketSales as today_revenue' => fn ($q) => $q->whereDate('created_at', today()),
            ], 'value');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Tipo de Ingresso')
                ->badge()
                ->color('info')
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->label('Descrição')
                ->toggleable(),

            TextColumn::make('is_active')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (bool $state) => $state ? 'Ativo' : 'Inativo')
                ->color(fn (bool $state) => $state ? 'success' : 'danger')
                ->sortable(),

            TextColumn::make('total_sales')
                ->label('Total de Vendas')
                ->sortable(),

            TextColumn::make('total_revenue')
                ->label('Receita Total')
                ->formatStateUsing(fn ($record) => $record->total_revenue ? format_money($record->total_revenue) : '-')
                ->sortable(),

            TextColumn::make('today_sales')
                ->label('Vendas Hoje')
                ->color('info')
                ->sortable(),

            TextColumn::make('today_revenue')
                ->label('Receita Hoje')
                ->formatStateUsing(fn ($record) => $record->today_revenue ? format_money($record->today_revenue) : '-')
                ->color('info')
                ->sortable(),
        ];
    }

    public function configureTable(Table $table): Table
    {
        return $table
            ->heading('Relatório por Tipo de Ingresso')
            ->description('Métricas detalhadas de cada tipo de ingresso')
            ->emptyStateHeading('Nenhum tipo de ingresso cadastrado')
            ->striped();
    }
}
