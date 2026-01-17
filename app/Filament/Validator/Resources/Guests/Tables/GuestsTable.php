<?php

namespace App\Filament\Validator\Resources\Guests\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Convidado')
                    ->description(fn ($record) => $record->document)
                    ->searchable(['name', 'document'])
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label('Evento')
                    ->sortable(),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                IconColumn::make('is_checked_in')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('checked_in_at')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->description(fn ($record) => $record->checked_in_at ? $record->checked_in_at->format('d/m/Y') : null)
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'name'),
                \Filament\Tables\Filters\TernaryFilter::make('is_checked_in')
                    ->label('Status de Check-in')
                    ->placeholder('Todos')
                    ->trueLabel('Confirmados')
                    ->falseLabel('Pendentes'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('checkIn')
                    ->label('Confirmar Entrada')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->hidden(fn ($record) => $record->is_checked_in)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Check-in')
                    ->modalDescription('Deseja confirmar a entrada deste convidado agora?')
                    ->action(function ($record) {
                        $record->update([
                            'is_checked_in' => true,
                            'checked_in_at' => now(),
                            'checked_in_by' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Check-in realizado!')
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\Action::make('undoCheckIn')
                    ->label('Estornar Entrada')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->is_checked_in)
                    ->requiresConfirmation()
                    ->modalHeading('Estornar Check-in')
                    ->modalDescription('Esta ação marcará o convidado como "Pendente" novamente.')
                    ->action(function ($record) {
                        $record->update([
                            'is_checked_in' => false,
                            'checked_in_at' => null,
                            'checked_in_by' => null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Check-in estornado.')
                            ->info()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                // Remover ações de criação
            ])
            ->bulkActions([
                // Remover ações em massa para segurança
            ]);
    }
}
