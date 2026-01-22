<?php

namespace App\Filament\Resources\ApprovalRequests\Tables;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRequestsTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                // Mobile Layout (Custom Card View)
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.approval-requests.tables.columns.mobile_card')
                    ->label('SOLICITAÇÕES')
                    ->hiddenFrom('md'),

                // Desktop Layout (Standard Table Columns)
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('guest_name')
                    ->label('Convidado')
                    ->description(fn (ApprovalRequest $record): string => $record->guest_document ?? 'Sem documento')
                    ->searchable(['guest_name', 'guest_document'])
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('requester.name')
                    ->label('Solicitante')
                    ->description(fn (ApprovalRequest $record): string => $record->requester->role->getLabel())
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (ApprovalRequest $record): ?string => $record->reviewed_at
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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(RequestStatus::class),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(RequestType::class),

                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'name')
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
                // Ver detalhes
                Action::make('view')
                    ->label('Detalhes')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Solicitação')
                    ->modalContent(fn (ApprovalRequest $record) => view('filament.modals.approval-request-details', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Fechar'))
                    ->modalWidth(\Filament\Support\Enums\Width::Large)
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),

                // Aprovar
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ApprovalRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar Solicitação')
                    ->modalDescription(fn (ApprovalRequest $record): string => "Aprovar a solicitação de {$record->type->getLabel()} para \"{$record->guest_name}\"?")
                    ->form([
                        Textarea::make('notes')
                            ->label('Observações (opcional)')
                            ->placeholder('Adicione uma nota se necessário...')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (ApprovalRequest $record, array $data): void {
                        try {
                            app(ApprovalRequestService::class)->approve(
                                $record,
                                auth()->user(),
                                $data['notes'] ?? null
                            );

                            $message = $record->type === RequestType::EMERGENCY_CHECKIN
                                ? 'Solicitação aprovada! Convidado criado e check-in realizado.'
                                : 'Solicitação aprovada! Convidado adicionado à lista.';

                            Notification::make()
                                ->title('Solicitação Aprovada')
                                ->body($message)
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

                // Aprovar em Outro Setor (quando convidado já existe em outro setor)
                Action::make('approveWithSectorUpdate')
                    ->label('Atualizar Setor')
                    ->icon('heroicon-m-arrow-path')
                    ->color('info')
                    ->visible(fn (ApprovalRequest $record): bool => $record->isPending() &&
                        $record->hasExistingGuest() &&
                        ! $record->existingGuestInSameSector()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar com Atualização de Setor')
                    ->modalDescription(fn (ApprovalRequest $record): string => sprintf(
                        'O convidado "%s" já existe em outro setor. Ao aprovar, o setor será atualizado para "%s".',
                        $record->guest_name,
                        $record->sector?->name ?? 'N/A'
                    ))
                    ->form([
                        Textarea::make('notes')
                            ->label('Observações (opcional)')
                            ->placeholder('Adicione uma nota se necessário...')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (ApprovalRequest $record, array $data): void {
                        try {
                            app(ApprovalRequestService::class)->approveWithSectorUpdate(
                                $record,
                                auth()->user(),
                                $data['notes'] ?? null
                            );

                            Notification::make()
                                ->title('Setor Atualizado')
                                ->body('O setor do convidado foi atualizado e a solicitação aprovada.')
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

                // Rejeitar
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (ApprovalRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Rejeitar Solicitação')
                    ->modalDescription(fn (ApprovalRequest $record): string => "Rejeitar a solicitação de {$record->type->getLabel()} para \"{$record->guest_name}\"?")
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo da rejeição')
                            ->placeholder('Informe o motivo da rejeição...')
                            ->required()
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (ApprovalRequest $record, array $data): void {
                        try {
                            app(ApprovalRequestService::class)->reject(
                                $record,
                                auth()->user(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title('Solicitação Rejeitada')
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

                // Reconsiderar (para rejeitados/cancelados)
                Action::make('reconsider')
                    ->label('Reconsiderar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn (ApprovalRequest $record): bool => $record->canBeReconsidered())
                    ->requiresConfirmation()
                    ->modalHeading('Reconsiderar Solicitação')
                    ->modalDescription(fn (ApprovalRequest $record): string => "Deseja reabrir a solicitação de \"{$record->guest_name}\"? Ela voltará para o status Pendente.")
                    ->form([
                        Textarea::make('notes')
                            ->label('Motivo da reconsideração (opcional)')
                            ->placeholder('Ex: Solicitação rejeitada por engano, informações adicionais recebidas...')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (ApprovalRequest $record, array $data): void {
                        try {
                            app(ApprovalRequestService::class)->reconsider(
                                $record,
                                auth()->user(),
                                $data['notes'] ?? null
                            );

                            Notification::make()
                                ->title('Solicitação Reconsiderada')
                                ->body('A solicitação voltou para análise.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao reconsiderar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),

                // Reverter aprovação (para aprovados por engano)
                Action::make('revert')
                    ->label('Reverter')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (ApprovalRequest $record): bool => $record->canBeReverted())
                    ->requiresConfirmation()
                    ->modalHeading('Reverter Aprovação')
                    ->modalDescription(fn (ApprovalRequest $record): string => "ATENÇÃO: Ao reverter, o convidado \"{$record->guest_name}\" será EXCLUÍDO da lista de convidados. A solicitação voltará para o status Pendente.")
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo da reversão')
                            ->placeholder('Ex: Aprovação feita por engano, documento inválido descoberto...')
                            ->required()
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (ApprovalRequest $record, array $data): void {
                        try {
                            app(ApprovalRequestService::class)->revert(
                                $record,
                                auth()->user(),
                                $data['reason']
                            );

                            Notification::make()
                                ->title('Aprovação Revertida')
                                ->body('O convidado foi removido e a solicitação voltou para análise.')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao reverter')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->bulkActions([
                BulkAction::make('approveSelected')
                    ->label('Aprovar')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar Solicitações')
                    ->modalDescription('Tem certeza que deseja aprovar todas as solicitações selecionadas?')
                    ->action(function (Collection $records): void {
                        $service = app(ApprovalRequestService::class);
                        $approved = 0;
                        $skipped = 0;

                        foreach ($records as $record) {
                            if ($record->isPending()) {
                                try {
                                    $service->approve($record, auth()->user());
                                    $approved++;
                                } catch (\Exception) {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title('Aprovação em massa concluída')
                            ->body("{$approved} aprovadas, {$skipped} ignoradas.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('rejectSelected')
                    ->label('Rejeitar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeitar Solicitações')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo da rejeição (aplicado a todas)')
                            ->required()
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $service = app(ApprovalRequestService::class);
                        $rejected = 0;
                        $skipped = 0;

                        foreach ($records as $record) {
                            if ($record->isPending()) {
                                try {
                                    $service->reject($record, auth()->user(), $data['reason']);
                                    $rejected++;
                                } catch (\Exception) {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title('Rejeição em massa concluída')
                            ->body("{$rejected} rejeitadas, {$skipped} ignoradas.")
                            ->warning()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('reconsiderSelected')
                    ->label('Reconsiderar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reconsiderar Solicitações')
                    ->modalDescription('As solicitações rejeitadas/canceladas selecionadas voltarão para análise.')
                    ->form([
                        Textarea::make('notes')
                            ->label('Motivo da reconsideração (aplicado a todas)')
                            ->placeholder('Ex: Revisão em lote...')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $service = app(ApprovalRequestService::class);
                        $reconsidered = 0;
                        $skipped = 0;

                        foreach ($records as $record) {
                            if ($record->canBeReconsidered()) {
                                try {
                                    $service->reconsider($record, auth()->user(), $data['notes'] ?? null);
                                    $reconsidered++;
                                } catch (\Exception) {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title('Reconsideração em massa concluída')
                            ->body("{$reconsidered} reconsideradas, {$skipped} ignoradas.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('revertSelected')
                    ->label('Reverter')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reverter Aprovações')
                    ->modalDescription('ATENÇÃO: Os convidados aprovados selecionados serão EXCLUÍDOS. As solicitações voltarão para Pendente.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo da reversão (aplicado a todas)')
                            ->placeholder('Ex: Revisão em lote...')
                            ->required()
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $service = app(ApprovalRequestService::class);
                        $reverted = 0;
                        $skipped = 0;

                        foreach ($records as $record) {
                            if ($record->canBeReverted()) {
                                try {
                                    $service->revert($record, auth()->user(), $data['reason']);
                                    $reverted++;
                                } catch (\Exception) {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        }

                        Notification::make()
                            ->title('Reversão em massa concluída')
                            ->body("{$reverted} revertidas, {$skipped} ignoradas.")
                            ->warning()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->poll('30s')
            ->emptyStateHeading('Nenhuma solicitação encontrada')
            ->emptyStateDescription('As solicitações de aprovação aparecerão aqui.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
