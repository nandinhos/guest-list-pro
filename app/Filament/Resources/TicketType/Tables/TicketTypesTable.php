<?php

namespace App\Filament\Resources\TicketType\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class TicketTypesTable
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
                    ->label('TIPO DE INGRESSO')
                    ->view('filament.resources.ticket-type.tables.columns.mobile_card')
                    ->getStateUsing(fn ($record) => $record)
                    ->hiddenFrom('md'),

                TextColumn::make('name')
                    ->label('Tipo de Ingresso')
                    ->description(fn ($record) => $record->event->name ?? '-')
                    ->searchable(['name'])
                    ->weight('bold')
                    ->visibleFrom('md'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                    ->formatStateUsing(fn ($state) => $state ? 'ATIVO' : 'INATIVO')
                    ->visibleFrom('md'),

                TextColumn::make('is_visible')
                    ->label('Visibilidade')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-m-eye' : 'heroicon-m-eye-slash')
                    ->formatStateUsing(fn ($state) => $state ? 'PÚBLICO' : 'OCULTO')
                    ->visibleFrom('md'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
