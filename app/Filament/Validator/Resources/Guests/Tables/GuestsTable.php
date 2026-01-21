<?php

namespace App\Filament\Validator\Resources\Guests\Tables;

use App\Services\GuestSearchService;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Mobile Layout (Custom Card View)
                \Filament\Tables\Columns\ViewColumn::make('mobile_card')
                    ->view('filament.validator.resources.guests.tables.columns.mobile_card')
                    ->label('LISTA')
                    ->hiddenFrom('md'),

                // Desktop Layout (Standard Table Columns)
                TextColumn::make('name')
                    ->label('NOME / DOC')
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record) => view('filament.validator.resources.guests.tables.columns.document_description', ['record' => $record]))
                    ->searchable(query: function ($query, string $search): void {
                        $searchService = app(GuestSearchService::class);
                        $normalizedSearch = $searchService->normalize($search);
                        $normalizedDocument = $searchService->normalizeDocument($search);
                        $searchTerms = array_filter(explode(' ', $normalizedSearch), fn ($term) => strlen($term) >= 2);
                        $query->where(function ($q) use ($normalizedSearch, $normalizedDocument, $searchTerms) {
                            $q->where('name_normalized', 'like', "%{$normalizedSearch}%");
                            foreach ($searchTerms as $term) {
                                $q->orWhere('name_normalized', 'like', "%{$term}%");
                            }
                            if (strlen($normalizedDocument) >= 3) {
                                $q->orWhere('document_normalized', 'like', "%{$normalizedDocument}%")
                                    ->orWhere('document', 'like', "%{$normalizedDocument}%");
                            }
                        });
                    })
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('SETOR')
                    ->badge()
                    ->color('info')
                    ->visibleFrom('md'),

                TextColumn::make('checked_in_at')
                    ->label('CHECK-IN')
                    ->default('Pendente')
                    ->formatStateUsing(fn ($state) => $state instanceof \DateTimeInterface ? $state->format('d/m/Y \à\s H:i') : $state)
                    ->color(fn ($record) => $record->is_checked_in ? 'success' : 'warning')
                    ->icon(fn ($record) => $record->is_checked_in ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                    ->iconColor(fn ($record) => $record->is_checked_in ? 'success' : 'warning')
                    ->visibleFrom('md'),

                TextColumn::make('validator.name')
                    ->label('VALIDADOR')
                    ->color('gray')
                    ->icon('heroicon-m-user')
                    ->placeholder('-')
                    ->extraAttributes(['class' => 'italic'])
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->visibleFrom('md'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('sector_id')
                    ->label('Setor')
                    ->relationship('sector', 'name', fn ($query) => $query->where('event_id', session('selected_event_id')))
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\SelectFilter::make('promoter_id')
                    ->label('Promoter')
                    ->options(fn () => \App\Models\User::whereHas('guests', fn ($q) => $q->where('event_id', session('selected_event_id')))->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\TernaryFilter::make('is_checked_in')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Confirmados')
                    ->falseLabel('Pendentes'),

                \Filament\Tables\Filters\TernaryFilter::make('possible_duplicates')
                    ->label('Duplicados')
                    ->placeholder('Todos')
                    ->trueLabel('Possíveis Duplicados')
                    ->falseLabel('Únicos')
                    ->queries(
                        true: fn ($query) => $query->whereIn('name_normalized', function ($subquery) {
                            $subquery->select('name_normalized')
                                ->from('guests')
                                ->where('event_id', session('selected_event_id'))
                                ->whereNotNull('name_normalized')
                                ->groupBy('name_normalized')
                                ->havingRaw('COUNT(*) > 1');
                        }),
                        false: fn ($query) => $query->whereNotIn('name_normalized', function ($subquery) {
                            $subquery->select('name_normalized')
                                ->from('guests')
                                ->where('event_id', session('selected_event_id'))
                                ->whereNotNull('name_normalized')
                                ->groupBy('name_normalized')
                                ->havingRaw('COUNT(*) > 1');
                        }),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->description(fn ($livewire) => sprintf(
                'Mostrando %d convidado(s) do evento selecionado',
                $livewire->getFilteredTableQuery()->count()
            ))
            ->recordActions([
                \Filament\Actions\Action::make('checkIn')
                    ->label('ENTRADA')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'hidden md:inline-flex'])
                    ->hidden(fn ($record) => $record?->is_checked_in ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Check-in')
                    ->modalDescription(fn ($record) => "Confirmar entrada de {$record->name}?")
                    ->modalSubmitActionLabel('Confirmar Entrada')
                    ->action(function ($record) {
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

                \Filament\Actions\Action::make('undoCheckIn')
                    ->label('Estornar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->extraAttributes(['class' => 'hidden md:inline-flex'])
                    ->visible(fn ($record) => ($record?->is_checked_in ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Estornar Check-in')
                    ->modalDescription(fn ($record) => "Estornar entrada de {$record->name}? Esta ação marcará o convidado como 'Pendente' novamente.")
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
            ])
            ->striped()
            ->defaultSort('name')
            ->poll('30s');
    }
}
