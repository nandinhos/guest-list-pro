<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers;

use App\Models\Veiculo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoresRelationManager extends RelationManager
{
    protected static string $relationship = 'monitores';

    protected static ?string $modelLabel = 'Monitor';

    protected static ?string $pluralModelLabel = 'Monitores';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cpf')
                    ->label('CPF')
                    ->searchable(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make(),
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

                TextInput::make('cpf')
                    ->label('CPF')
                    ->required()
                    ->maxLength(14)
                    ->placeholder('000.000.000-00'),

                Select::make('veiculo_id')
                    ->label('Veículo')
                    ->options(function (RelationManager $livewire) {
                        $excursaoId = $livewire->getOwnerRecord()?->id;

                        return Veiculo::where('excursao_id', $excursaoId)
                            ->get()
                            ->mapWithKeys(fn ($v) => [
                                $v->id => $v->tipo->label().($v->placa ? ' ('.$v->placa.')' : ''),
                            ]);
                    })
                    ->required(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] = session('selected_event_id');
        $data['criado_por'] = auth()->id();

        return $data;
    }
}
