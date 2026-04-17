<?php

namespace App\Filament\Widgets;

use App\Models\EventAssignment;
use App\Models\Guest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AdminPromoterQuotaWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $eventId = session('selected_event_id');

        if (! $eventId) {
            return EventAssignment::query()->whereRaw('1 = 0');
        }

        return EventAssignment::query()
            ->where('event_id', $eventId)
            ->where('role', \App\Enums\UserRole::PROMOTER)
            ->with(['user', 'sector']);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('user.name')
                ->label('Promoter')
                ->searchable()
                ->sortable(),

            TextColumn::make('sector.name')
                ->label('Setor')
                ->sortable(),

            TextColumn::make('guest_count')
                ->label('Cadastrados')
                ->getStateUsing(fn (EventAssignment $record): int => Guest::where('promoter_id', $record->user_id)
                    ->where('event_id', $record->event_id)
                    ->where('sector_id', $record->sector_id)
                    ->count())
                ->sortable(),

            TextColumn::make('guest_limit')
                ->label('Limite')
                ->sortable(),

            TextColumn::make('remaining')
                ->label('Restantes')
                ->getStateUsing(fn (EventAssignment $record): int => max(0, $record->guest_limit - Guest::where('promoter_id', $record->user_id)
                    ->where('event_id', $record->event_id)
                    ->where('sector_id', $record->sector_id)
                    ->count()))
                ->color(fn (int $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

            TextColumn::make('usage')
                ->label('Ocupação')
                ->getStateUsing(function (EventAssignment $record): string {
                    $used = Guest::where('promoter_id', $record->user_id)
                        ->where('event_id', $record->event_id)
                        ->where('sector_id', $record->sector_id)
                        ->count();
                    $percentage = $record->guest_limit > 0 ? round(($used / $record->guest_limit) * 100) : 0;

                    return "{$percentage}%";
                })
                ->color(function (EventAssignment $record): string {
                    $used = Guest::where('promoter_id', $record->user_id)
                        ->where('event_id', $record->event_id)
                        ->where('sector_id', $record->sector_id)
                        ->count();
                    $percentage = $record->guest_limit > 0 ? ($used / $record->guest_limit) * 100 : 0;

                    return $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
                }),

            TextColumn::make('start_time')
                ->label('Início')
                ->formatStateUsing(fn ($state) => $state ? $state->format('H:i') : '-'),

            TextColumn::make('end_time')
                ->label('Fim')
                ->formatStateUsing(fn ($state) => $state ? $state->format('H:i') : '-'),

            TextColumn::make('time_status')
                ->label('Janela')
                ->getStateUsing(function (EventAssignment $record): string {
                    if (! $record->start_time && ! $record->end_time) {
                        return 'Livre';
                    }

                    $now = now()->format('H:i:s');
                    $isActive = true;

                    if ($record->start_time && $now < $record->start_time->format('H:i:s')) {
                        $isActive = false;
                    }

                    if ($record->end_time && $now > $record->end_time->format('H:i:s')) {
                        $isActive = false;
                    }

                    return $isActive ? 'Ativa' : 'Inativa';
                })
                ->color(fn (string $state): string => $state === 'Ativa' ? 'success' : 'warning'),
        ];
    }

    public function configureTable(Table $table): Table
    {
        return $table
            ->heading('Cotas de Promoters')
            ->description('Visão geral das permissões e cotas de cada promoter')
            ->emptyStateHeading('Nenhuma permissão de promoter encontrada')
            ->emptyStateDescription('Cadastre promoters no evento selecionado para gerenciar suas cotas.')
            ->striped();
    }
}
