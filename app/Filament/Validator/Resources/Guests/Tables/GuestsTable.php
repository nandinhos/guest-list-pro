<?php

namespace App\Filament\Validator\Resources\Guests\Tables;

use App\Models\Guest;
use App\Services\GuestSearchService;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Convidado / Documento')
                    ->formatStateUsing(function ($state, $record, $livewire) {
                        // Obtém o termo de busca atual da tabela Filament
                        $searchTerm = $livewire->getTableSearch() ?? '';
                        $isPartialMatch = false;

                        if (! empty($searchTerm)) {
                            $searchService = app(GuestSearchService::class);
                            $normalizedSearch = $searchService->normalize($searchTerm);
                            $normalizedName = $record->name_normalized ?? $searchService->normalize($record->name);

                            // Calcula similaridade
                            $similarity = $searchService->calculateSimilarity($normalizedSearch, $normalizedName);

                            // Match parcial: encontrou mas não é exato (< 95%)
                            $isPartialMatch = $similarity > 0.3 && $similarity < 0.95;
                        }

                        return view('filament.components.guest-name-column', [
                            'name' => $state,
                            'document' => $record->document ?? '-',
                            'isPartialMatch' => $isPartialMatch,
                        ]);
                    })
                    ->html()
                    ->searchable(query: function ($query, string $search): void {
                        $searchService = app(GuestSearchService::class);

                        // Normaliza a busca: remove acentos e converte para lowercase
                        $normalizedSearch = $searchService->normalize($search);
                        $normalizedDocument = $searchService->normalizeDocument($search);

                        // Divide o termo de busca em palavras para fuzzy search
                        $searchTerms = array_filter(explode(' ', $normalizedSearch), fn ($term) => strlen($term) >= 2);

                        $query->where(function ($q) use ($normalizedSearch, $normalizedDocument, $searchTerms) {
                            // Busca exata por nome normalizado
                            $q->where('name_normalized', 'like', "%{$normalizedSearch}%");

                            // Busca fuzzy: cada termo individualmente (encontra "Joao Silva" com "João da Silva")
                            foreach ($searchTerms as $term) {
                                $q->orWhere('name_normalized', 'like', "%{$term}%");
                            }

                            // Busca por documento normalizado
                            if (strlen($normalizedDocument) >= 3) {
                                $q->orWhere('document_normalized', 'like', "%{$normalizedDocument}%")
                                    ->orWhere('document', 'like', "%{$normalizedDocument}%");
                            }
                        });
                    })
                    ->sortable(),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_checked_in')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('checked_in_at')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->description(fn ($record) => $record->checked_in_at ? $record->checked_in_at->format('d/m/Y') : null)
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('sector_id')
                    ->label('Setor')
                    ->relationship('sector', 'name', fn ($query) => $query->where('event_id', session('selected_event_id')))
                    ->searchable()
                    ->preload(),

                \Filament\Tables\Filters\TernaryFilter::make('is_checked_in')
                    ->label('Status de Check-in')
                    ->placeholder('Todos')
                    ->trueLabel('Confirmados')
                    ->falseLabel('Pendentes'),

                \Filament\Tables\Filters\SelectFilter::make('checked_in_recent')
                    ->label('Check-in Recente')
                    ->options([
                        '15' => 'Últimos 15 minutos',
                        '30' => 'Últimos 30 minutos',
                        '60' => 'Última 1 hora',
                    ])
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $minutes = (int) $data['value'];
                            $query->where('checked_in_at', '>=', now()->subMinutes($minutes));
                        }
                    }),

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
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::Modal)
            ->filtersFormColumns(2)
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
                    ->size('lg')
                    ->hidden(fn ($record) => $record->is_checked_in)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Check-in')
                    ->modalDescription('Deseja confirmar a entrada deste convidado agora?')
                    ->action(function ($record) {
                        try {
                            DB::transaction(function () use ($record) {
                                $guest = Guest::lockForUpdate()->find($record->id);

                                if ($guest->is_checked_in) {
                                    throw new \Exception('checkin_exists');
                                }

                                $guest->update([
                                    'is_checked_in' => true,
                                    'checked_in_at' => now(),
                                    'checked_in_by' => auth()->id(),
                                ]);
                            });

                            // Log de Sucesso
                            DB::table('checkin_attempts')->insert([
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
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            $isAlreadyCheckedIn = $e->getMessage() === 'checkin_exists';

                            // Log de Falha / Tentativa Duplicada
                            DB::table('checkin_attempts')->insert([
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
                    ->link() // Mantém como link para ser menos proeminente que a entrada, mas acessível
                    ->visible(fn ($record) => $record->is_checked_in)
                    ->requiresConfirmation()
                    ->modalHeading('Estornar Check-in')
                    ->modalDescription('Esta ação marcará o convidado como "Pendente" novamente.')
                    ->action(function ($record) {
                        try {
                            DB::transaction(function () use ($record) {
                                $guest = Guest::lockForUpdate()->find($record->id);

                                if (! $guest->is_checked_in) {
                                    throw new \Exception('guest_not_checked_in');
                                }

                                $guest->update([
                                    'is_checked_in' => false,
                                    'checked_in_at' => null,
                                    'checked_in_by' => null,
                                ]);
                            });

                            // Log de Estorno
                            DB::table('checkin_attempts')->insert([
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
                                ->title('Check-in estornado.')
                                ->info()
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
            ->toolbarActions([
                // Remover ações de criação
            ])
            ->bulkActions([
                // Remover ações em massa para segurança
            ]);
    }
}
