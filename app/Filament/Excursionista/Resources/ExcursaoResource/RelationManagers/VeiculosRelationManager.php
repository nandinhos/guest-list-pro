<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers;

use App\Enums\TipoVeiculo;
use App\Models\Veiculo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VeiculosRelationManager extends RelationManager
{
    protected static string $relationship = 'veiculos';

    protected static ?string $modelLabel = 'Veículo';

    protected static ?string $pluralModelLabel = 'Veículos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => TipoVeiculo::tryFrom($state)?->label() ?? $state),

                TextColumn::make('placa')
                    ->label('Placa')
                    ->placeholder('—'),

                TextColumn::make('monitores_count')
                    ->label('Monitores')
                    ->counts('monitores'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo')
                    ->options(TipoVeiculo::class)
                    ->required()
                    ->native(false),

                TextInput::make('placa')
                    ->label('Placa')
                    ->placeholder('ABC-1234')
                    ->maxLength(10),
            ]);
    }
}
