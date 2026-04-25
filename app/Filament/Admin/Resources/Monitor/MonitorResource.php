<?php

namespace App\Filament\Admin\Resources\Monitor;

use App\Filament\Admin\Resources\Monitor\Pages\ListMonitores;
use App\Models\Monitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitorResource extends Resource
{
    protected static ?string $model = Monitor::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Monitor';

    protected static ?string $pluralModelLabel = 'Monitores';

    protected static ?string $navigationLabel = 'Monitores';

    protected static ?string $slug = 'monitores';

    protected static ?int $navigationSort = 101;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($selectedEventId = session('selected_event_id')) {
            $query->where('event_id', $selectedEventId);
        }

        return $query->with(['veiculo.excursao', 'criadoPor']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('nome')
                    ->label('NOME')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('document_number')
                    ->label('DOCUMENTO')
                    ->formatStateUsing(fn ($record) => $record->document_type->getLabel().': '.$record->document_number)
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('veiculo.excursao.nome')
                    ->label('EXCURSÃO')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('veiculo.tipo')
                    ->label('VEÍCULO')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\TipoVeiculo ? $state->label() : \App\Enums\TipoVeiculo::tryFrom($state)?->label() ?? $state),

                \Filament\Tables\Columns\TextColumn::make('criadoPor.name')
                    ->label('RESPONSÁVEL')
                    ->badge()
                    ->color('info'),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('CRIADO EM')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonitores::route('/'),
        ];
    }
}
