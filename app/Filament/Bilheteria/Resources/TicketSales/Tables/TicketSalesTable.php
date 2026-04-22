<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Tables;

use App\Enums\PaymentMethod;
use Filament\Actions\Action;
use App\Models\TicketSale;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['guest', 'seller', 'event', 'ticketType', 'refundRequest']))
            ->columns([
                \Filament\Tables\Columns\ViewColumn::make('mobile_card')
                    ->label('VENDA')
                    ->view('filament.bilheteria.resources.ticket-sales.mobile_card')
                    ->getStateUsing(fn ($record) => $record)
                    ->hiddenFrom('md'),

                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('buyer_name')
                    ->label('Comprador')
                    ->description(fn ($record) => $record->buyer_document ?? '-')
                    ->searchable(['buyer_name', 'buyer_document'])
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('ticketType.name')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($record) => format_money($record->value))
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PaymentMethod::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state) => PaymentMethod::tryFrom($state)?->getColor() ?? 'gray')
                    ->visibleFrom('md'),

                TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->formatStateUsing(fn ($record) => format_datetime($record->created_at))
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('ticket_type')
                    ->label('Tipo de Ingresso')
                    ->options(fn () => \App\Models\TicketType::query()
                        ->where('event_id', session('selected_event_id'))
                        ->where('is_active', true)
                        ->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $typeId) => $q->where('ticket_type_id', $typeId)
                    ))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('sector')
                    ->label('Setor')
                    ->options(fn () => \App\Models\Sector::query()
                        ->where('event_id', session('selected_event_id'))
                        ->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $sectorId) => $q->where('sector_id', $sectorId)
                    ))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('Pagamento')
                    ->options(PaymentMethod::class),

                SelectFilter::make('sold_by')
                    ->label('Vendedor')
                    ->relationship('seller', 'name', fn ($query) => $query->where('role', \App\Enums\UserRole::BILHETERIA))
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Período')
                    ->form([
                        DateTimePicker::make('from')
                            ->label('Início'),
                        DateTimePicker::make('until')
                            ->label('Até'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $dateTime) => $q->where('created_at', '>=', $dateTime))
                        ->when($data['until'], fn ($q, $dateTime) => $q->where('created_at', '<=', $dateTime))),

                Filter::make('has_refund')
                    ->label('Estorno')
                    ->query(fn ($query, array $data) => $query
                        ->when($data['value'] === 'refunded', fn ($q) => $q->where('is_refunded', true))
                        ->when($data['value'] === 'pending_refund', fn ($q) => $q->whereHas('refundRequest', fn ($rq) => $rq->where('status', 'pending')))),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->recordActions([
                Action::make('viewDetails')
                    ->label('')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Venda')
                    ->modalContent(function (TicketSale $record) {
                        return view('filament.modals.ticket-sale-details', ['record' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Fechar'))
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),

                Action::make('requestRefund')
                    ->label('')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('warning')
                    ->visible(function (TicketSale $record): bool {
                        if ($record->is_refunded) {
                            return false;
                        }
                        if ($record->refundRequest && $record->refundRequest->isPending()) {
                            return false;
                        }
                        return true;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Solicitar Estorno')
                    ->modalDescription(fn (TicketSale $record): string => "Solicitar estorno para venda #{$record->id}\n\nComprador: {$record->buyer_name}\nValor: R$ ".number_format($record->value, 2, ',', '.'))
                    ->form([
                        Textarea::make('refund_reason')
                            ->label('Motivo do Estorno')
                            ->placeholder('Descreva o motivo para solicitação de estorno...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (TicketSale $record, array $data) {
                        $reason = $data['refund_reason'] ?? null;

                        if (! $reason) {
                            \Filament\Notifications\Notification::make()
                                ->title('Motivo obrigatório')
                                ->body('Informe o motivo do estorno.')
                                ->warning()
                                ->send();
                            return;
                        }

                        try {
                            app(\App\Services\RefundRequestService::class)->createRefundRequest(
                                $record,
                                auth()->user(),
                                $reason
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('Estorno Solicitado')
                                ->body("Solicitação de estorno para venda #{$record->id} enviada para aprovação.")
                                ->success()
                                ->send();

                            $record->refresh();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao solicitar estorno')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->defaultSort('created_at', 'desc');
    }
}