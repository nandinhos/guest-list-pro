<?php

namespace App\Filament\Admin\Resources\Excursao\RelationManagers;

use App\Enums\DocumentType;
use App\Models\Monitor;
use App\Models\Veiculo;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MonitoresRelationManager extends RelationManager
{
    protected static string $relationship = 'monitores';

    protected static ?string $modelLabel = 'Monitor';

    protected static ?string $pluralModelLabel = 'Monitores';

    protected function canCreate(): bool
    {
        return auth()->user()->can('create', Monitor::class);
    }

    protected function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', $record);
    }

    protected function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete', $record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('NOME')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_number')
                    ->label('DOCUMENTO')
                    ->formatStateUsing(fn ($record) => $record->document_type->getLabel().': '.$record->document_number)
                    ->searchable(),

                TextColumn::make('veiculo.tipo')
                    ->label('VEÍCULO')
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\TipoVeiculo ? $state->label() : \App\Enums\TipoVeiculo::tryFrom($state)?->label() ?? $state),

                TextColumn::make('veiculo.placa')
                    ->label('PLACA'),

                TextColumn::make('criadoPor.name')
                    ->label('RESPONSÁVEL')
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['event_id'] = $this->getOwnerRecord()->event_id;
                        $data['criado_por'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(150),

                Select::make('document_type')
                    ->label('Tipo do Documento')
                    ->options(DocumentType::class)
                    ->required()
                    ->native(false),

                TextInput::make('document_number')
                    ->label('Número do Documento')
                    ->required()
                    ->maxLength(20),

                Select::make('veiculo_id')
                    ->label('Veículo')
                    ->options(function ($livewire): array {
                        return Veiculo::query()
                            ->where('excursao_id', $livewire->getOwnerRecord()->id)
                            ->get()
                            ->mapWithKeys(fn (Veiculo $v): array => [
                                $v->id => $v->tipo->label(),
                            ])
                            ->all();
                    })
                    ->nullable()
                    ->native(false),
            ]);
    }
}
