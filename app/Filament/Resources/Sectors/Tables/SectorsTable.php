<?php

namespace App\Filament\Resources\Sectors\Tables;

use Filament\Actions\BulkActionGroup;
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
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.sectors.tables.columns.mobile_card')
                    ->label('Dados do Setor')
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
                TextColumn::make('created_at')
                    ->dateTime()
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
            ->actionsColumnLabel('Ações')
            ->recordActions([
                EditAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
