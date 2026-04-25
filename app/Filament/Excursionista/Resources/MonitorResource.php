<?php

namespace App\Filament\Excursionista\Resources;

use App\Filament\Excursionista\Resources\MonitorResource\Pages\CreateMonitor;
use App\Filament\Excursionista\Resources\MonitorResource\Pages\EditMonitor;
use App\Filament\Excursionista\Resources\MonitorResource\Pages\ListMonitores;
use App\Models\Monitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitorResource extends Resource
{
    protected static ?string $model = Monitor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Monitor';

    protected static ?string $pluralModelLabel = 'Monitores';

    protected static ?string $navigationLabel = 'Monitores';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($selectedEventId = session('selected_event_id')) {
            $query->where('event_id', $selectedEventId);
        }

        return $query->where('criado_por', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Dados do Monitor')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('nome')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Nome do monitor'),

                        \Filament\Forms\Components\Select::make('document_type')
                            ->label('Tipo do Documento')
                            ->options(\App\Enums\DocumentType::class)
                            ->required()
                            ->native(false),

                        \Filament\Forms\Components\TextInput::make('document_number')
                            ->label('Número do Documento')
                            ->required()
                            ->maxLength(20),

                        \Filament\Forms\Components\Select::make('veiculo_id')
                            ->label('Veículo')
                            ->options(function () {
                                $eventId = session('selected_event_id');
                                $userId = auth()->id();

                                return \App\Models\Veiculo::query()
                                    ->whereHas('excursao', function ($q) use ($eventId, $userId) {
                                        $q->where('event_id', $eventId)
                                            ->where('criado_por', $userId);
                                    })
                                    ->with('excursao')
                                    ->get()
                                    ->mapWithKeys(fn ($v) => [
                                        $v->id => $v->excursao->nome.' — '.$v->tipo->label().($v->placa ? ' ('.$v->placa.')' : ''),
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ViewColumn::make('mobile_card')
                    ->view('filament.excursionista.resources.monitor-resource.tables.columns.mobile_card')
                    ->label('MONITORES')
                    ->hiddenFrom('md'),

                \Filament\Tables\Columns\TextColumn::make('nome')
                    ->label('NOME')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                \Filament\Tables\Columns\TextColumn::make('document_number')
                    ->label('DOCUMENTO')
                    ->formatStateUsing(fn ($record) => $record->document_type->getLabel().': '.$record->document_number)
                    ->searchable()
                    ->visibleFrom('md'),

                \Filament\Tables\Columns\TextColumn::make('veiculo.excursao.nome')
                    ->label('EXCURSÃO')
                    ->searchable()
                    ->visibleFrom('md'),

                \Filament\Tables\Columns\TextColumn::make('veiculo.tipo')
                    ->label('VEÍCULO')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\TipoVeiculo ? $state->label() : \App\Enums\TipoVeiculo::tryFrom($state)?->label() ?? $state)
                    ->visibleFrom('md'),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('CRIADO EM')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonitores::route('/'),
            'create' => CreateMonitor::route('/create'),
            'edit' => EditMonitor::route('/{record}/edit'),
        ];
    }
}
