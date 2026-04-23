<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers;

use App\Enums\DocumentType;
use App\Enums\TipoVeiculo;
use App\Models\Veiculo;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VeiculosRelationManager extends RelationManager
{
    protected static string $relationship = 'veiculos';

    protected static ?string $modelLabel = 'Veículo';

    protected static ?string $pluralModelLabel = 'Veículos';

    protected function canCreate(): bool
    {
        return auth()->user()->can('create', Veiculo::class);
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
            ->modifyQueryUsing(fn ($query) => $query->with('monitores'))
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
                DeleteAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.excursionista.resources.excursao-resource.relation-managers.veiculos.tables.columns.mobile_card')
                    ->label('VEÍCULOS')
                    ->hiddenFrom('md'),

                TextColumn::make('tipo')
                    ->label('TIPO')
                    ->visibleFrom('md'),

                TextColumn::make('placa')
                    ->label('PLACA')
                    ->visibleFrom('md'),

                TextColumn::make('monitores_count')
                    ->label('MONITORES')
                    ->visibleFrom('md'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Dados do Veículo')
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoVeiculo::class)
                            ->required()
                            ->native(false),

                        TextInput::make('placa')
                            ->label('Placa')
                            ->placeholder('ABC-1234')
                            ->maxLength(10),
                    ]),

                Repeater::make('monitores')
                    ->relationship()
                    ->label('Monitores')
                    ->schema([
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
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['event_id'] = session('selected_event_id');
                        $data['veiculo_id'] = $this->getOwnerRecord()->id;
                        $data['criado_por'] = auth()->id();

                        return $data;
                    })
                    ->columns(3),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] = session('selected_event_id');
        $data['criado_por'] = auth()->id();

        return $data;
    }
}
