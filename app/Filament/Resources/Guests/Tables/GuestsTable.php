<?php

namespace App\Filament\Resources\Guests\Tables;

use App\Rules\CheckinRule;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['event', 'sector', 'promoter', 'validator']))
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
                    ->formatStateUsing(fn ($state) => format_datetime($state))
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->formatStateUsing(fn ($state) => format_datetime($state))
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
            ->actions([
                Action::make('checkIn')
                    ->label('Check-in')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->hidden(fn ($record) => $record?->is_checked_in ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Check-in')
                    ->modalDescription(fn ($record) => "Confirmar entrada de {$record->name}?")
                    ->modalSubmitActionLabel('Confirmar Entrada')
                    ->action(function ($record) {
                        $user = auth()->user();
                        $result = CheckinRule::validateCheckin($user, $record->qr_token);

                        if (! $result['allowed']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro no check-in')
                                ->body($result['message'])
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                                $guest = \App\Models\Guest::lockForUpdate()->find($record->id);
                                if ($guest->is_checked_in) {
                                    throw new \Exception('checkin_exists');
                                }
                                $guest->update([
                                    'is_checked_in' => true,
                                    'checked_in_at' => now(),
                                    'checked_in_by' => auth()->id(),
                                ]);
                            });
                            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                                'event_id' => $record->event_id,
                                'validator_id' => auth()->id(),
                                'guest_id' => $record->id,
                                'result' => 'success',
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Check-in realizado!')
                                ->body("Entrada confirmada para {$record->name}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            $isAlreadyCheckedIn = $e->getMessage() === 'checkin_exists';
                            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                                'event_id' => $record->event_id,
                                'validator_id' => auth()->id(),
                                'guest_id' => $record->id,
                                'result' => $isAlreadyCheckedIn ? 'already_checked_in' : 'error',
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title($isAlreadyCheckedIn ? 'Check-in já realizado!' : 'Erro no check-in')
                                ->body($isAlreadyCheckedIn ? 'Este convidado já entrou.' : $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('undoCheckIn')
                    ->label('Estornar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->visible(fn ($record) => ($record?->is_checked_in ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Estornar Check-in')
                    ->modalDescription(fn ($record) => "Estornar entrada de {$record->name}?")
                    ->modalSubmitActionLabel('Confirmar Estorno')
                    ->action(function ($record) {
                        try {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                                $guest = \App\Models\Guest::lockForUpdate()->find($record->id);
                                if (! $guest->is_checked_in) {
                                    throw new \Exception('guest_not_checked_in');
                                }
                                $guest->update([
                                    'is_checked_in' => false,
                                    'checked_in_at' => null,
                                    'checked_in_by' => null,
                                ]);
                            });
                            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                                'event_id' => $record->event_id,
                                'validator_id' => auth()->id(),
                                'guest_id' => $record->id,
                                'result' => 'estorno',
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Check-in estornado')
                                ->body("{$record->name} voltou para a fila de entrada.")
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao estornar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('downloadQr')
                    ->label('Baixar QR')
                    ->hiddenLabel()
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->tooltip('Baixar QR Code')
                    ->extraAttributes(['class' => 'hidden md:inline-flex'])
                    ->action(function ($record) {
                        if (empty($record->qr_token)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Este convidado não possui um token de QR Code.')
                                ->danger()
                                ->send();

                            return;
                        }

                        return response()->streamDownload(
                            fn () => print (QrCode::format('svg')->size(200)->generate($record->qr_token)),
                            "qr-code-{$record->qr_token}.svg"
                        );
                    }),

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
            ])
            ->recordUrl(null); // Desativar link na linha para forçar uso do botão Editar
    }
}
