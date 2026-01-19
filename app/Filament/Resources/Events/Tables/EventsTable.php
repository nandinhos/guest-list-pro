<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ViewColumn::make('event_card')
                    ->view('filament.tables.columns.event-card')
                    ->label('') // Ocultar label da coluna
                    ->alignLeft(),
            ])
            ->contentGrid([
                'default' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->recordUrl(
                fn (\App\Models\Event $record): string => \App\Filament\Resources\Events\Pages\EditEvent::getUrl(['record' => $record]),
            )
            ->filters([
                //
            ])
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
