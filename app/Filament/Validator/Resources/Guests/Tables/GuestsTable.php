<?php

namespace App\Filament\Validator\Resources\Guests\Tables;

use App\Enums\DocumentType;
use App\Models\Sector;
use App\Services\ApprovalRequestService;
use App\Services\GuestSearchService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['sector', 'validator']))
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
                    ->placeholder('Pendente')
                    ->formatStateUsing(fn ($state) => $state ? format_datetime($state) : null)
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
            ->recordUrl(null)
            ->recordActions([
                Action::make('checkIn')
                    ->label('ENTRADA')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'hidden md:inline-flex'])
                    ->hidden(fn ($record) => $record?->is_checked_in ?? false)
                    ->slideOver()
                    ->modalHeading('Confirmar Check-in')
                    ->modalWidth('md')
                    ->schema([
                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('guest_name')
                                    ->label('Nome do Convidado')
                                    ->content(fn ($record) => $record->name)
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                                \Filament\Forms\Components\Placeholder::make('document')
                                    ->label('Documento')
                                    ->content(fn ($record) => $record->document),
                                \Filament\Forms\Components\Placeholder::make('sector')
                                    ->label('Setor')
                                    ->content(fn ($record) => $record->sector?->name ?? '-'),
                            ])->columns(1),
                    ])
                    ->action(function ($record, \Livewire\Component $livewire) {
                        static::doCheckIn($record);
                        $livewire->resetTable();
                    }),

                Action::make('undoCheckIn')
                    ->label('Estornar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->button()
                    ->outlined()
                    ->size('sm')
                    ->extraAttributes(['class' => 'hidden md:inline-flex'])
                    ->visible(fn ($record) => ($record?->is_checked_in ?? false))
                    ->slideOver()
                    ->modalHeading('Estornar Check-in')
                    ->modalWidth('md')
                    ->schema([
                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('guest_name')
                                    ->label('Nome do Convidado')
                                    ->content(fn ($record) => $record->name)
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-warning-600']),
                                \Filament\Forms\Components\Placeholder::make('document')
                                    ->label('Documento')
                                    ->content(fn ($record) => $record->document),
                                \Filament\Forms\Components\Placeholder::make('sector')
                                    ->label('Setor')
                                    ->content(fn ($record) => $record->sector?->name ?? '-'),
                                \Filament\Forms\Components\Placeholder::make('checked_in_at')
                                    ->label('Check-in realizado em')
                                    ->content(fn ($record) => $record->checked_in_at ? format_datetime($record->checked_in_at) : '-'),
                            ])->columns(1),
                    ])
                    ->action(function ($record, \Livewire\Component $livewire) {
                        static::doUndoCheckIn($record);
                        $livewire->resetTable();
                    }),
            ])
            ->striped()
            ->defaultSort('name')
            ->poll('30s')
            ->headerActions([
                Action::make('emergencyCheckinRequest')
                    ->label('Não está na lista')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->color('warning')
                    ->modalHeading('Solicitar Check-in Emergencial')
                    ->modalDescription('Preencha os dados do convidado que não está na lista. A solicitação será enviada para aprovação do administrador.')
                    ->slideOver()
                    ->schema([
                        TextInput::make('guest_name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nome do convidado'),

                        Select::make('guest_document_type')
                            ->label('Tipo de documento')
                            ->options(DocumentType::class)
                            ->default(DocumentType::CPF)
                            ->required()
                            ->live(),

                        TextInput::make('guest_document')
                            ->label('Documento')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Número do documento')
                            ->mask(fn (Get $get) => in_array($get('guest_document_type'), [DocumentType::CPF, DocumentType::CPF->value], true) ? '999.999.999-99' : null),

                        Select::make('sector_id')
                            ->label('Setor')
                            ->options(fn () => Sector::where('event_id', session('selected_event_id'))->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Textarea::make('notes')
                            ->label('Motivo / Observações')
                            ->placeholder('Ex: Convidado de última hora do artista principal, autorizado verbalmente pelo produtor...')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->modalSubmitActionLabel('Enviar Solicitação')
                    ->action(function (array $data): void {
                        try {
                            $service = app(ApprovalRequestService::class);

                            // Verificar duplicidade (documento = bloqueante, nome = aviso)
                            $duplicate = $service->checkForDuplicates(
                                session('selected_event_id'),
                                $data['guest_name'],
                                $data['guest_document'] ?? null
                            );

                            if ($duplicate) {
                                if ($duplicate['level'] === 'error') {
                                    // Documento duplicado = bloqueia
                                    Notification::make()
                                        ->title('Solicitação Bloqueada')
                                        ->body($duplicate['message'])
                                        ->danger()
                                        ->persistent()
                                        ->send();

                                    return;
                                }

                                // Nome duplicado = aviso, continua
                                Notification::make()
                                    ->title('Atenção: Possível Duplicidade')
                                    ->body($duplicate['message'].' A solicitação será enviada para revisão.')
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }

                            $request = $service->createEmergencyCheckinRequest(
                                \Illuminate\Support\Facades\Auth::user(),
                                session('selected_event_id'),
                                $data['sector_id'],
                                [
                                    'name' => $data['guest_name'],
                                    'document' => $data['guest_document'],
                                    'document_type' => $data['guest_document_type'],
                                ],
                                $data['notes']
                            );

                            Notification::make()
                                ->title('Solicitação enviada!')
                                ->body("Solicitação #{$request->id} criada. Peça para o convidado aguardar ao lado enquanto o administrador aprova.")
                                ->success()
                                ->persistent()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao criar solicitação')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function doCheckIn($record): void
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                $guest = \App\Models\Guest::lockForUpdate()->find($record->id);
                if ($guest->is_checked_in) {
                    throw new \Exception('checkin_exists');
                }
                $guest->checkIn(\Illuminate\Support\Facades\Auth::id());
            });

            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                'event_id' => $record->event_id,
                'validator_id' => \Illuminate\Support\Facades\Auth::id(),
                'guest_id' => $record->id,
                'result' => 'success',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Notification::make()
                ->title('Check-in realizado!')
                ->body("Entrada confirmada para {$record->name}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            $isAlreadyCheckedIn = $e->getMessage() === 'checkin_exists';
            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                'event_id' => $record->event_id,
                'validator_id' => \Illuminate\Support\Facades\Auth::id(),
                'guest_id' => $record->id,
                'result' => $isAlreadyCheckedIn ? 'already_checked_in' : 'error',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Notification::make()
                ->title($isAlreadyCheckedIn ? 'Check-in já realizado!' : 'Erro no check-in')
                ->body($isAlreadyCheckedIn ? 'Este convidado já entrou.' : $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function doUndoCheckIn($record): void
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                $guest = \App\Models\Guest::lockForUpdate()->find($record->id);
                if (! $guest->is_checked_in) {
                    throw new \Exception('guest_not_checked_in');
                }
                $guest->undoCheckIn();
            });

            \Illuminate\Support\Facades\DB::table('checkin_attempts')->insert([
                'event_id' => $record->event_id,
                'validator_id' => \Illuminate\Support\Facades\Auth::id(),
                'guest_id' => $record->id,
                'result' => 'estorno',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Notification::make()
                ->title('Check-in estornado')
                ->body("{$record->name} voltou para a fila de entrada.")
                ->warning()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao estornar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
