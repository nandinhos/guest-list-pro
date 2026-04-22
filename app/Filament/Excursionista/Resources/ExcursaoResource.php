<?php

namespace App\Filament\Excursionista\Resources;

use App\Filament\Excursionista\Resources\ExcursaoResource\Pages\CreateExcursao;
use App\Filament\Excursionista\Resources\ExcursaoResource\Pages\EditExcursao;
use App\Filament\Excursionista\Resources\ExcursaoResource\Pages\ListExcursoes;
use App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers\MonitoresRelationManager;
use App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers\VeiculosRelationManager;
use App\Models\Excursao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExcursaoResource extends Resource
{
    protected static ?string $model = Excursao::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $modelLabel = 'Excursão';

    protected static ?string $pluralModelLabel = 'Excursões';

    protected static ?string $navigationLabel = 'Excursões';

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
                \Filament\Schemas\Components\Section::make('Dados da Excursão')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('nome')
                            ->label('Nome da Excursão')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Ex: Caravanas São Paulo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('veiculos_count')
                    ->label('Veículos')
                    ->counts('veiculos'),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VeiculosRelationManager::class,
            MonitoresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExcursoes::route('/'),
            'create' => CreateExcursao::route('/create'),
            'edit' => EditExcursao::route('/{record}/edit'),
        ];
    }
}
