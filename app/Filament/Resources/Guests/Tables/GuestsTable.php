<?php

namespace App\Filament\Resources\Guests\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.guests.tables.columns.mobile_card')
                    ->label('Dados do Convidado')
                    ->hiddenFrom('md'),

                TextColumn::make('name')
                    ->label('Convidado / Documento')
                    ->description(fn (\App\Models\Guest $record): string => $record->document ?? '-')
                    ->searchable(['name', 'document'])
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('event.name')
                    ->label('Evento / Setor')
                    ->description(fn (\App\Models\Guest $record): string => $record->sector->name ?? '-')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('promoter.name')
                    ->label('Promoter')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('is_checked_in')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Check-in' : 'Pendente')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('validator.name')
                    ->label('Validado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),

                TextColumn::make('checked_in_at')
                    ->label('Check-in em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
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
            ->actionsColumnLabel('Ações')
            ->recordActions([
                EditAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
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
