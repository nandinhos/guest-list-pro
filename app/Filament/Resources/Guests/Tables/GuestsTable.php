<?php

namespace App\Filament\Resources\Guests\Tables;

use Filament\Actions\Action;
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
                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Convidado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document')
                    ->label('Documento')
                    ->searchable(),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Exportar Todos (CSV)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function () {
                        $records = \App\Models\Guest::with(['event', 'sector', 'promoter', 'validator'])->get();

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
                        }, 'todos-convidados-'.now()->format('d-m-Y').'.csv');
                    }),
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
