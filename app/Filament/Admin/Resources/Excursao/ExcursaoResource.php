<?php

namespace App\Filament\Admin\Resources\Excursao;

use App\Filament\Admin\Resources\Excursao\Pages\CreateExcursao;
use App\Filament\Admin\Resources\Excursao\Pages\EditExcursao;
use App\Filament\Admin\Resources\Excursao\Pages\ListExcursoes;
use App\Filament\Admin\Resources\Excursao\RelationManagers\MonitoresRelationManager;
use App\Filament\Admin\Resources\Excursao\RelationManagers\VeiculosRelationManager;
use App\Models\Event;
use App\Models\Excursao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExcursaoResource extends Resource
{
    protected static ?string $model = Excursao::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $modelLabel = 'Excursão';

    protected static ?string $pluralModelLabel = 'Excursões';

    protected static ?string $navigationLabel = 'Excursões';

    protected static ?string $slug = 'excursoes';

    protected static string|UnitEnum|null $navigationGroup = 'Excursionistas';

    protected static ?int $navigationSort = 100;

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($selectedEventId = session('selected_event_id')) {
            $query->where('event_id', $selectedEventId);
        }

        return $query->with(['criadoPor', 'veiculos']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Dados da Excursão')
                    ->schema([
                        \Filament\Forms\Components\Select::make('event_id')
                            ->label('Evento')
                            ->options(Event::query()->pluck('name', 'id'))
                            ->default(fn () => session('selected_event_id'))
                            ->required()
                            ->native(false)
                            ->searchable(),

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
                    ->label('NOME')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('criadoPor.name')
                    ->label('RESPONSÁVEL')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                \Filament\Tables\Columns\TextColumn::make('veiculos_count')
                    ->label('VEÍCULOS')
                    ->counts('veiculos'),

                \Filament\Tables\Columns\TextColumn::make('monitores_count')
                    ->label('MONITORES')
                    ->counts('monitores'),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('CRIADA EM')
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
