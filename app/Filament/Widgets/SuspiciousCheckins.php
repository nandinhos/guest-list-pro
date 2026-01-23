<?php

namespace App\Filament\Widgets;

use App\Models\CheckinAttempt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SuspiciousCheckins extends BaseWidget
{
    protected static ?string $heading = 'Tentativas de Check-in Suspeitas / Falhas';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $eventId = session('selected_event_id');

        $query = CheckinAttempt::query()
            ->where('result', '!=', 'success')
            ->latest();

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\ViewColumn::make('mobile_card')
                    ->view('filament.widgets.suspicious-checkins.mobile_card')
                    ->label('TENTATIVAS')
                    ->hiddenFrom('md'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('validator.name')
                    ->label('Validador')
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Convidado Alvo')
                    ->placeholder('-')
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('result')
                    ->label('Resultado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'already_checked_in' => 'warning',
                        'error' => 'danger',
                        'estorno' => 'info',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Evento')
                    ->limit(20)
                    ->visible(fn () => ! session('selected_event_id'))
                    ->visibleFrom('md'),
            ])
            ->actions([
                //
            ])
            ->emptyStateHeading('Nenhuma tentativa suspeita')
            ->emptyStateDescription('Todas as tentativas de check-in foram bem sucedidas.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->poll('30s');
    }
}
