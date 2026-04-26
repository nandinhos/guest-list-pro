<?php

namespace App\Filament\Admin\Pages;

use App\Enums\DocumentType;
use App\Enums\TipoVeiculo;
use App\Models\Event;
use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\Veiculo;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use UnitEnum;

class ExcursoesGestao extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Gestão de Excursionistas';

    protected static ?string $title = 'Gestão de Excursionistas';

    protected static ?string $slug = 'reports/excursoes';

    protected static ?int $navigationSort = 1;

    protected static UnitEnum|string|null $navigationGroup = 'Excursionistas';

    protected string $view = 'filament.admin.pages.excursoes-gestao';

    public ?int $selectedEventId = null;

    public string $activeTab = 'excursoes';

    public function mount(): void
    {
        $this->selectedEventId = session('selected_event_id') ?? Event::first()?->id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(1)->schema([
                        Select::make('selectedEventId')
                            ->label('Evento')
                            ->options(fn () => Event::orderByDesc('created_at')->pluck('name', 'id'))
                            ->default($this->selectedEventId)
                            ->live()
                            ->afterStateUpdated(fn () => $this->resetTable()),
                    ]),
                ])->columns(1),
        ]);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function getTabCount(string $tab): int
    {
        if (! $this->selectedEventId) {
            return 0;
        }

        return match ($tab) {
            'excursoes' => Excursao::where('event_id', $this->selectedEventId)->count(),
            'veiculos' => Veiculo::whereHas('excursao', fn ($q) => $q->where('event_id', $this->selectedEventId))->count(),
            'monitores' => Monitor::where('event_id', $this->selectedEventId)->count(),
            default => 0,
        };
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'veiculos' => $this->buildVeiculosTable($table),
            'monitores' => $this->buildMonitoresTable($table),
            default => $this->buildExcursoesTable($table),
        };
    }

    private function buildExcursoesTable(Table $table): Table
    {
        return $table
            ->query(
                Excursao::query()
                    ->where('event_id', $this->selectedEventId ?? 0)
                    ->with(['criadoPor:id,name', 'veiculos.monitores'])
                    ->orderBy('nome')
            )
            ->emptyStateHeading($this->selectedEventId ? 'Nenhuma excursão cadastrada' : 'Selecione um evento')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('criadoPor.name')
                    ->label('Responsável')
                    ->sortable(),

                TextColumn::make('veiculos_count')
                    ->label('Veículos')
                    ->state(fn (Excursao $record): int => $record->veiculos->count())
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                TextColumn::make('monitores_count')
                    ->label('Monitores')
                    ->state(fn (Excursao $record): int => $record->veiculos->flatMap->monitores->count())
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                TableAction::make('createExcursao')
                    ->label('Nova Excursão')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->disabled(fn () => ! $this->selectedEventId)
                    ->form([
                        TextInput::make('nome')
                            ->label('Nome da Excursão')
                            ->required()
                            ->maxLength(150),
                    ])
                    ->action(function (array $data): void {
                        Excursao::create([
                            'nome' => $data['nome'],
                            'event_id' => $this->selectedEventId,
                            'criado_por' => Auth::id(),
                        ]);
                        Notification::make()->title('Excursão criada')->success()->send();
                        $this->resetTable();
                    }),
            ])
            ->actions([
                TableAction::make('editExcursao')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->form([
                        TextInput::make('nome')
                            ->label('Nome da Excursão')
                            ->required()
                            ->maxLength(150),
                    ])
                    ->fillForm(fn (Excursao $record): array => ['nome' => $record->nome])
                    ->action(function (Excursao $record, array $data): void {
                        $record->update(['nome' => $data['nome']]);
                        Notification::make()->title('Excursão atualizada')->success()->send();
                        $this->resetTable();
                    }),

                TableAction::make('deleteExcursao')
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Isso também excluirá todos os veículos e monitores desta excursão.')
                    ->action(function (Excursao $record): void {
                        $record->delete();
                        Notification::make()->title('Excursão excluída')->success()->send();
                        $this->resetTable();
                    }),
            ]);
    }

    private function buildVeiculosTable(Table $table): Table
    {
        $eventId = $this->selectedEventId ?? 0;

        return $table
            ->query(
                Veiculo::query()
                    ->whereHas('excursao', fn ($q) => $q->where('event_id', $eventId))
                    ->with(['excursao', 'monitores'])
                    ->orderBy('created_at', 'desc')
            )
            ->emptyStateHeading($this->selectedEventId ? 'Nenhum veículo cadastrado' : 'Selecione um evento')
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (TipoVeiculo $state): string => $state->label())
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('placa')
                    ->label('Placa')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('excursao.nome')
                    ->label('Excursão')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('monitores_count')
                    ->label('Monitores')
                    ->state(fn (Veiculo $record): int => $record->monitores->count())
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                TableAction::make('createVeiculo')
                    ->label('Novo Veículo')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->disabled(fn () => ! $this->selectedEventId)
                    ->form([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(collect(TipoVeiculo::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                            ->required(),

                        TextInput::make('placa')
                            ->label('Placa')
                            ->maxLength(10)
                            ->placeholder('Opcional'),

                        Select::make('excursao_id')
                            ->label('Excursão')
                            ->options(fn () => Excursao::where('event_id', $eventId)->orderBy('nome')->pluck('nome', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data): void {
                        Veiculo::create([
                            'tipo' => $data['tipo'],
                            'placa' => $data['placa'] ?: null,
                            'excursao_id' => $data['excursao_id'],
                        ]);
                        Notification::make()->title('Veículo criado')->success()->send();
                        $this->resetTable();
                    }),
            ])
            ->actions([
                TableAction::make('editVeiculo')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->form([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(collect(TipoVeiculo::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                            ->required(),

                        TextInput::make('placa')
                            ->label('Placa')
                            ->maxLength(10),

                        Select::make('excursao_id')
                            ->label('Excursão')
                            ->options(fn () => Excursao::where('event_id', $eventId)->orderBy('nome')->pluck('nome', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->fillForm(fn (Veiculo $record): array => [
                        'tipo' => $record->tipo->value,
                        'placa' => $record->placa,
                        'excursao_id' => $record->excursao_id,
                    ])
                    ->action(function (Veiculo $record, array $data): void {
                        $record->update([
                            'tipo' => $data['tipo'],
                            'placa' => $data['placa'] ?: null,
                            'excursao_id' => $data['excursao_id'],
                        ]);
                        Notification::make()->title('Veículo atualizado')->success()->send();
                        $this->resetTable();
                    }),

                TableAction::make('deleteVeiculo')
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Isso também excluirá todos os monitores deste veículo.')
                    ->action(function (Veiculo $record): void {
                        $record->delete();
                        Notification::make()->title('Veículo excluído')->success()->send();
                        $this->resetTable();
                    }),
            ]);
    }

    private function buildMonitoresTable(Table $table): Table
    {
        $eventId = $this->selectedEventId ?? 0;

        return $table
            ->query(
                Monitor::query()
                    ->where('event_id', $eventId)
                    ->with(['veiculo.excursao', 'criadoPor:id,name'])
                    ->orderBy('nome')
            )
            ->emptyStateHeading($this->selectedEventId ? 'Nenhum monitor cadastrado' : 'Selecione um evento')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('documento')
                    ->label('Documento')
                    ->state(fn (Monitor $record): string => $record->document_type->getLabel().': '.$record->document_number)
                    ->searchable(query: fn ($query, string $search) => $query->where('document_number', 'like', "%{$search}%")),

                TextColumn::make('veiculo.tipo')
                    ->label('Veículo')
                    ->formatStateUsing(fn ($state, Monitor $record): string => $record->veiculo
                        ? $record->veiculo->tipo->label().($record->veiculo->placa ? ' · '.$record->veiculo->placa : '')
                        : 'Sem veículo'
                    )
                    ->default('Sem veículo'),

                TextColumn::make('veiculo.excursao.nome')
                    ->label('Excursão')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('criadoPor.name')
                    ->label('Responsável'),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                TableAction::make('createMonitor')
                    ->label('Novo Monitor')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->disabled(fn () => ! $this->selectedEventId)
                    ->form([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(150),

                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(collect(DocumentType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()]))
                            ->required()
                            ->default(DocumentType::CPF->value),

                        TextInput::make('document_number')
                            ->label('Número do Documento')
                            ->required()
                            ->maxLength(20)
                            ->rules(fn (callable $get) => [
                                Rule::unique('monitores', 'document_number')
                                    ->where('event_id', $this->selectedEventId)
                                    ->where('document_type', $get('document_type')),
                            ])
                            ->validationMessages(['unique' => 'Este documento já está cadastrado para este evento.']),

                        Select::make('veiculo_id')
                            ->label('Veículo')
                            ->options(fn () => Veiculo::whereHas('excursao', fn ($q) => $q->where('event_id', $eventId))
                                ->with('excursao')
                                ->get()
                                ->mapWithKeys(fn ($v) => [$v->id => $v->excursao->nome.' — '.$v->tipo->label().($v->placa ? ' ('.$v->placa.')' : '')])
                            )
                            ->placeholder('Sem veículo')
                            ->searchable(),
                    ])
                    ->action(function (array $data): void {
                        Monitor::create([
                            'nome' => $data['nome'],
                            'document_type' => $data['document_type'],
                            'document_number' => $data['document_number'],
                            'veiculo_id' => $data['veiculo_id'] ?: null,
                            'event_id' => $this->selectedEventId,
                            'criado_por' => Auth::id(),
                        ]);
                        Notification::make()->title('Monitor criado')->success()->send();
                        $this->resetTable();
                    }),
            ])
            ->actions([
                TableAction::make('editMonitor')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->form([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(150),

                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(collect(DocumentType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()]))
                            ->required(),

                        TextInput::make('document_number')
                            ->label('Número do Documento')
                            ->required()
                            ->maxLength(20)
                            ->rules(fn (callable $get, ?Monitor $record) => [
                                Rule::unique('monitores', 'document_number')
                                    ->where('event_id', $this->selectedEventId)
                                    ->where('document_type', $get('document_type'))
                                    ->ignore($record?->id),
                            ])
                            ->validationMessages(['unique' => 'Este documento já está cadastrado para este evento.']),

                        Select::make('veiculo_id')
                            ->label('Veículo')
                            ->options(fn () => Veiculo::whereHas('excursao', fn ($q) => $q->where('event_id', $eventId))
                                ->with('excursao')
                                ->get()
                                ->mapWithKeys(fn ($v) => [$v->id => $v->excursao->nome.' — '.$v->tipo->label().($v->placa ? ' ('.$v->placa.')' : '')])
                            )
                            ->placeholder('Sem veículo')
                            ->searchable(),
                    ])
                    ->fillForm(fn (Monitor $record): array => [
                        'nome' => $record->nome,
                        'document_type' => $record->document_type->value,
                        'document_number' => $record->document_number,
                        'veiculo_id' => $record->veiculo_id,
                    ])
                    ->action(function (Monitor $record, array $data): void {
                        $record->update([
                            'nome' => $data['nome'],
                            'document_type' => $data['document_type'],
                            'document_number' => $data['document_number'],
                            'veiculo_id' => $data['veiculo_id'] ?: null,
                        ]);
                        Notification::make()->title('Monitor atualizado')->success()->send();
                        $this->resetTable();
                    }),

                TableAction::make('deleteMonitor')
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Monitor $record): void {
                        $record->delete();
                        Notification::make()->title('Monitor excluído')->success()->send();
                        $this->resetTable();
                    }),
            ]);
    }
}
