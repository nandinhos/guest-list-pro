<?php

namespace App\Filament\Resources\RefundRequest;

use App\Enums\RefundStatus;
use App\Models\RefundRequest;
use App\Services\RefundRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestsTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ticketSale', 'requester', 'reviewer', 'ticketSale.event']))
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.refund-requests.tables.columns.mobile_card')
                    ->label('ESTORNOS')
                    ->getStateUsing(fn ($record) => $record)
                    ->hiddenFrom('md'),

                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('ticketSale.id')
                    ->label('Venda')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('ticketSale.buyer_name')
                    ->label('Comprador')
                    ->description(fn (RefundRequest $record): string => $record->ticketSale->buyer_document ?? 'Sem documento')
                    ->searchable(['buyer_name', 'buyer_document'])
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('ticketSale.event.name')
                    ->label('Evento')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('ticketSale.value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->description(fn (RefundRequest $record): string => $record->requester->role->getLabel())
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(fn (RefundRequest $record): string => $record->reason)
                    ->visibleFrom('md'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (RefundStatus $state): string => $state->getLabel())
                    ->color(fn (RefundStatus $state): string => $state->getColor())
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (RefundRequest $record): ?string => $record->reviewed_at
                        ? 'Revisado: '.$record->reviewed_at->format('d/m/Y H:i')
                        : null
                    )
                    ->visibleFrom('md'),

                TextColumn::make('reviewer.name')
                    ->label('Revisado por')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),

                TextColumn::make('review_notes')
                    ->label('Observações')
                    ->limit(30)
                    ->tooltip(fn (RefundRequest $record): ?string => $record->review_notes)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(RefundStatus::class),

                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], function ($q) use ($data) {
                        $q->whereHas('ticketSale.event', function ($q2) use ($data) {
                            $q2->where('id', $data['value']);
                        });
                    }))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('requester_id')
                    ->label('Solicitante')
                    ->relationship('requester', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                Action::make('view')
                    ->label('Detalhes')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes do Estorno')
                    ->modalContent(fn (RefundRequest $record) => view('filament.modals.refund-request-details', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Fechar'))
                    ->modalWidth(\Filament\Support\Enums\Width::Large)
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),

                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (RefundRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar Estorno')
                    ->modalDescription(fn (RefundRequest $record): string => "Aprovar o estorno de R$ {$record->ticketSale->value} para \"{$record->ticketSale->buyer_name}\"?\n\nMotivo: {$record->reason}")
                    ->form([
                        Textarea::make('notes')
                            ->label('Observações (opcional)')
                            ->placeholder('Adicione uma nota se necessário...')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (RefundRequest $record, array $data): void {
                        try {
                            app(RefundRequestService::class)->approve(
                                $record,
                                auth()->user(),
                                $data['notes'] ?? null
                            );

                            Notification::make()
                                ->title('Estorno Aprovado')
                                ->body("A venda #{$record->ticketSale->id} foi marcada como estornada.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao aprovar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),

                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (RefundRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Rejeitar Estorno')
                    ->modalDescription(fn (RefundRequest $record): string => "Rejeitar o estorno de R$ {$record->ticketSale->value} para \"{$record->ticketSale->buyer_name}\"?")
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo da rejeição')
                            ->placeholder('Informe o motivo da rejeição...')
                            ->required()
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (RefundRequest $record, array $data): void {
                        try {
                            app(RefundRequestService::class)->reject(
                                $record,
                                auth()->user(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title('Estorno Rejeitado')
                                ->body('O solicitante será notificado.')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao rejeitar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->poll('30s')
            ->emptyStateHeading('Nenhum estorno encontrado')
            ->emptyStateDescription('As solicitações de estorno aparecerão aqui.')
            ->emptyStateIcon('heroicon-o-arrow-uturn-left');
    }
}