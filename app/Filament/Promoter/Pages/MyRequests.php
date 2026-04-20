<?php

namespace App\Filament\Promoter\Pages;

use App\Enums\DocumentType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\PromoterPermission;
use App\Models\Sector;
use App\Services\ApprovalRequestService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRequest')
                ->label('Nova Solicitação')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->modalHeading('Criar Solicitação de Inclusão')
                ->form([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->options(fn () => Event::whereIn('id',
                                PromoterPermission::where('user_id', auth()->id())->pluck('event_id')
                            )->pluck('name', 'id')->toArray())
                            ->live()
                            ->searchable()
                            ->required(),

                        Select::make('sector_id')
                            ->label('Setor')
                            ->options(function (Get $get) {
                                $eventId = $get('event_id');
                                if (! $eventId) {
                                    return [];
                                }

                                $permission = PromoterPermission::where('user_id', auth()->id())
                                    ->where('event_id', $eventId)
                                    ->first();

                                if (! $permission) {
                                    return [];
                                }

                                if (is_null($permission->sector_id)) {
                                    return Sector::where('event_id', $eventId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }

                                return Sector::whereIn('id',
                                    PromoterPermission::where('user_id', auth()->id())
                                        ->where('event_id', $eventId)
                                        ->pluck('sector_id')
                                )->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    TextInput::make('guest_name')
                        ->label('Nome Completo')
                        ->required()
                        ->maxLength(255),

                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        Select::make('guest_document_type')
                            ->label('Tipo de Documento')
                            ->options(DocumentType::class)
                            ->default(DocumentType::CPF->value)
                            ->live()
                            ->required(),

                        TextInput::make('guest_document')
                            ->label('Documento')
                            ->maxLength(20),
                    ]),

                    TextInput::make('guest_email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(255),

                    Textarea::make('notes')
                        ->label('Observações (opcional)')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $service = app(ApprovalRequestService::class);
                    $request = $service->createGuestInclusionRequest(
                        auth()->user(),
                        $data['event_id'],
                        $data['sector_id'],
                        [
                            'name' => $data['guest_name'],
                            'document' => $data['guest_document'] ?? null,
                            'document_type' => $data['guest_document_type'] ?? null,
                            'email' => $data['guest_email'] ?? null,
                        ],
                        $data['notes'] ?? null
                    );

                    Notification::make()
                        ->title('Solicitação criada')
                        ->body("Solicitação #{$request->id} enviada para aprovação.")
                        ->success()
                        ->send();
                }),
        ];
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
