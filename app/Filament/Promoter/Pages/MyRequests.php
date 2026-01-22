<?php

namespace App\Filament\Promoter\Pages;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MyRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-inbox';

    protected static UnitEnum|string|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Minhas Solicitações';

    protected static ?string $title = 'Minhas Solicitações';

    protected static ?int $navigationSort = 2;

    protected static bool $isContentFullWidth = true;

    protected string $view = 'filament.promoter.pages.my-requests';

    public static function getNavigationBadge(): ?string
    {
        $count = ApprovalRequest::pending()
            ->byRequester(auth()->id())
            ->byType(RequestType::GUEST_INCLUSION)
            ->forEvent(session('selected_event_id'))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ApprovalRequest::query()
                    ->byRequester(auth()->id())
                    ->byType(RequestType::GUEST_INCLUSION)
                    ->forEvent(session('selected_event_id'))
                    ->with(['sector', 'reviewer'])
            )
            ->columns([
                // Mobile Layout (Custom Card View)
                ViewColumn::make('mobile_card')
                    ->view('filament.promoter.pages.requests.mobile_card')
                    ->label('SOLICITAÇÕES')
                    ->hiddenFrom('md'),

                // Desktop Layout (Standard Table Columns)
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('guest_name')
                    ->label('Convidado')
                    ->description(fn (ApprovalRequest $record): string => $record->guest_document ?? 'Sem documento')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->visibleFrom('md'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('reviewer.name')
                    ->label('Revisado por')
                    ->placeholder('-')
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('reviewer_notes')
                    ->label('Observações')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(RequestStatus::class),
            ])
            ->actions([
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

                // Cancelar (apenas pendentes)
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (ApprovalRequest $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Solicitação')
                    ->modalDescription('Tem certeza que deseja cancelar esta solicitação? Esta ação não pode ser desfeita.')
                    ->action(function (ApprovalRequest $record): void {
                        try {
                            app(ApprovalRequestService::class)->cancel($record, auth()->user());

                            Notification::make()
                                ->title('Solicitação cancelada')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao cancelar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->poll('30s')
            ->emptyStateHeading('Nenhuma solicitação')
            ->emptyStateDescription('Suas solicitações de inclusão de convidados aparecerão aqui quando você atingir o limite ou estiver fora do horário permitido.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
