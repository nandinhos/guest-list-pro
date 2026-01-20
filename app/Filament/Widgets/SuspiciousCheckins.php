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

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CheckinAttempt::query()
                    ->where('result', '!=', 'success')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('d/m H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validator.name')
                    ->label('Validador'),
                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Convidado Alvo')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('result')
                    ->label('Resultado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'already_checked_in' => 'warning',
                        'error' => 'danger',
                        'estorno' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Evento')
                    ->limit(20),
            ])
            ->actions([
                //
            ]);
    }
}
