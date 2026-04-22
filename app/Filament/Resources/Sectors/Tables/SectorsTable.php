<?php

namespace App\Filament\Resources\Sectors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class SectorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['event'])->withCount('ticketSales'))
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.sectors.tables.columns.mobile_card')
                    ->label('Dados do Setor')
                    ->getStateUsing(fn ($record) => $record)
                    ->hiddenFrom('md'),

                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label('Setor')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('capacity')
                    ->label('Capacidade')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Ilimitada')
                    ->visibleFrom('md'),
                TextColumn::make('ticket_sales_count')
                    ->label('Ocupação')
                    ->state(fn ($record) => $record->capacity ? round(($record->ticket_sales_count / $record->capacity) * 100).'%' : 'N/A')
                    ->description(fn ($record) => $record->ticket_sales_count.' de '.($record->capacity ?? '∞'))
                    ->badge()
                    ->icon(fn ($state) => match (true) {
                        (int) $state >= 90 => 'heroicon-m-exclamation-triangle',
                        (int) $state >= 70 => 'heroicon-m-information-circle',
                        default => 'heroicon-m-check-circle',
                    })
                    ->color(fn ($state) => match (true) {
                        (int) $state >= 90 => 'danger',
                        (int) $state >= 70 => 'warning',
                        default => 'success',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()->extraAttributes(['class' => 'hidden md:inline-flex']),
                DeleteAction::make()->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->recordActions([
                EditAction::make()->extraAttributes(['class' => 'hidden md:inline-flex']),
                DeleteAction::make()->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
