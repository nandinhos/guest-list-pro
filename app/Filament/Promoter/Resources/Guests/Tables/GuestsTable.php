<?php

namespace App\Filament\Promoter\Resources\Guests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['sector']))
            ->columns([
                // Mobile Layout (Custom Card View)
                \Filament\Tables\Columns\ViewColumn::make('mobile_card')
                    ->view('filament.promoter.resources.guests.tables.columns.mobile_card')
                    ->label('LISTA')
                    ->hiddenFrom('md'),

                // Desktop Layout (Standard Table Columns)
                TextColumn::make('name')
                    ->label('NOME / DOC')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->description(fn ($record) => view('filament.promoter.resources.guests.tables.columns.document_description', ['record' => $record]))
                    ->searchable(query: function ($query, string $search): void {
                        $searchService = app(\App\Services\GuestSearchService::class);
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
                                ->where('promoter_id', auth()->id())
                                ->whereNotNull('name_normalized')
                                ->groupBy('name_normalized')
                                ->havingRaw('COUNT(*) > 1');
                        }),
                        false: fn ($query) => $query->whereNotIn('name_normalized', function ($subquery) {
                            $subquery->select('name_normalized')
                                ->from('guests')
                                ->where('event_id', session('selected_event_id'))
                                ->where('promoter_id', auth()->id())
                                ->whereNotNull('name_normalized')
                                ->groupBy('name_normalized')
                                ->havingRaw('COUNT(*) > 1');
                        }),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->description(fn ($livewire) => sprintf(
                'Mostrando %d convidado(s) da sua lista no evento selecionado',
                $livewire->getFilteredTableQuery()->count()
            ))
            ->actions([
                Action::make('downloadQr')
                    ->label('QR Code')
                    ->hiddenLabel()
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->tooltip('Baixar QR Code')
                    ->extraAttributes(['class' => 'hidden md:inline-flex']) // Esconde no mobile nativamente
                    ->action(function (\App\Models\Guest $record) {
                        try {
                            if (empty($record->qr_token)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Este convidado não possui um token de QR Code gerado.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            return response()->streamDownload(
                                fn () => print(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($record->qr_token)),
                                "qr-code-{$record->qr_token}.svg"
                            );
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao gerar QR Code')
                                ->body('Tente novamente mais tarde.')
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']), // Esconde no mobile nativamente
            ])
            ->actionsColumnLabel('AÇÕES')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null); // Desativar link na linha para forçar uso do botão Editar
    }
}
