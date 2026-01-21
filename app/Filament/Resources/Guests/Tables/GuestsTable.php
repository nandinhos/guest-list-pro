<?php

namespace App\Filament\Resources\Guests\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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

                TextColumn::make('event.name')
                    ->label('Evento / Setor')
                    ->description(fn (\App\Models\Guest $record): string => $record->sector->name ?? '-')
                    ->sortable(),

                TextColumn::make('promoter.name')
                    ->label('Promoter')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_checked_in')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('validator.name')
                    ->label('Validado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('checked_in_at')
                    ->label('Check-in em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('sector_id')
                    ->label('Setor')
                    ->relationship('sector', 'name')
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('promoter_id')
                    ->label('Promoter')
                    ->relationship('promoter', 'name')
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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('exportSelected')
                        ->label('Exportar Selecionados (CSV)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            return response()->streamDownload(function () use ($records) {
                                $file = fopen('php://output', 'w');
                                fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM
                                fputcsv($file, ['ID', 'Evento', 'Setor', 'Nome', 'Documento', 'Email', 'Status', 'Promoter', 'Validado Por', 'Data Check-in'], ';');

                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->id,
                                        $record->event?->name,
                                        $record->sector?->name,
                                        $record->name,
                                        $record->document,
                                        $record->email,
                                        $record->is_checked_in ? 'Sim' : 'Não',
                                        $record->promoter?->name,
                                        $record->validator?->name,
                                        $record->checked_in_at?->format('d/m/Y H:i'),
                                    ], ';');
                                }
                                fclose($file);
                            }, 'convidados-selecionados-'.now()->format('d-m-Y').'.csv');
                        }),
                ]),
            ]);
    }
}
