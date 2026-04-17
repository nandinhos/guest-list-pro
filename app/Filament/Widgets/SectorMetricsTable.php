<?php

namespace App\Filament\Widgets;

use App\Models\Sector;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SectorMetricsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $eventId = session('selected_event_id');

        return Sector::query()
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->withCount([
                'guests as total_guests',
                'guests as checked_in_count' => fn ($q) => $q->where('is_checked_in', true),
                'guests as pending_count' => fn ($q) => $q->where('is_checked_in', false),
            ])
            ->withSum([
                'ticketSales as revenue' => fn ($q) => $q->whereNotNull('ticket_type_id'),
            ], 'value');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Setor')
                ->searchable()
                ->sortable(),

            TextColumn::make('total_guests')
                ->label('Convidados')
                ->sortable(),

            TextColumn::make('capacity')
                ->label('Capacidade')
                ->sortable(),

            TextColumn::make('occupancy')
                ->label('Ocupação')
                ->getStateUsing(fn (Sector $record) => $record->capacity > 0
                    ? round(($record->total_guests / $record->capacity) * 100, 1).'%'
                    : '0%')
                ->color(fn (Sector $record) => $this->getOccupancyColor($record)),

            TextColumn::make('checked_in_count')
                ->label('Entraram')
                ->color('success')
                ->sortable(),

            TextColumn::make('pending_count')
                ->label('Pendentes')
                ->color('warning')
                ->sortable(),

            TextColumn::make('revenue')
                ->label('Receita')
                ->formatStateUsing(fn ($record) => $record->revenue ? format_money($record->revenue) : '-')
                ->sortable(),
        ];
    }

    private function getOccupancyColor(Sector $record): string
    {
        if ($record->capacity <= 0) {
            return 'gray';
        }

        $occupancy = ($record->total_guests / $record->capacity) * 100;

        if ($occupancy >= 90) {
            return 'danger';
        }

        if ($occupancy >= 70) {
            return 'warning';
        }

        return 'success';
    }

    public function configureTable(Table $table): Table
    {
        return $table
            ->heading('Métricas por Setor')
            ->description('Visão detalhada de cada setor com occupancy, check-in e receita')
            ->emptyStateHeading('Nenhum setor encontrado')
            ->striped();
    }
}
