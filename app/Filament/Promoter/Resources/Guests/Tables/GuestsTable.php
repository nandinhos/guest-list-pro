<?php

namespace App\Filament\Promoter\Resources\Guests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->label('Convidado / Documento')
                    ->description(fn (\App\Models\Guest $record): string => $record->document ?? '-')
                    ->searchable(['name', 'document'])
                    ->sortable(),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_checked_in')
                    ->label('Check-in')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('checked_in_at')
                    ->label('Horário')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('sector_id')
                    ->label('Setor')
                    ->relationship('sector', 'name', fn ($query) => $query->where('event_id', session('selected_event_id')))
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('is_checked_in')
                    ->label('Status')
                    ->options([
                        true => 'Check-in Realizado',
                        false => 'Pendente',
                    ]),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
