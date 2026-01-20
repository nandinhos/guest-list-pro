<?php

namespace App\Filament\Validator\Resources\Guests\Tables;

use App\Models\Guest;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Convidado / Documento')
                    ->description(fn ($record) => $record->document ?? '-')
                    ->searchable(['name', 'document'])
                    ->sortable(),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->searchable()
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
                \Filament\Tables\Filters\SelectFilter::make('sector_id')
                    ->label('Setor')
                    ->relationship('sector', 'name', fn ($query) => $query->where('event_id', session('selected_event_id')))
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\TernaryFilter::make('is_checked_in')
                    ->label('Status de Check-in')
                    ->placeholder('Todos')
                    ->trueLabel('Confirmados')
                    ->falseLabel('Pendentes'),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
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
                        try {
                            DB::transaction(function () use ($record) {
                                $guest = Guest::lockForUpdate()->find($record->id);

                                if ($guest->is_checked_in) {
                                    throw new \Exception('Check-in já foi realizado por outro operador.');
                                }

                                $guest->update([
                                    'is_checked_in' => true,
                                    'checked_in_at' => now(),
                                    'checked_in_by' => auth()->id(),
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Check-in realizado!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro no check-in')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
                        try {
                            DB::transaction(function () use ($record) {
                                $guest = Guest::lockForUpdate()->find($record->id);

                                if (! $guest->is_checked_in) {
                                    throw new \Exception('Este convidado não possui check-in para estornar.');
                                }

                                $guest->update([
                                    'is_checked_in' => false,
                                    'checked_in_at' => null,
                                    'checked_in_by' => null,
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Check-in estornado.')
                                ->info()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao estornar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
