<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\RelationManagers;

use App\Enums\DocumentType;
use Filament\Actions\CreateAction;
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

                TextColumn::make('document_type')
                    ->label('Documento')
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '—'),

                TextColumn::make('document_number')
                    ->label('Número')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make(),
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
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] = session('selected_event_id');
        $data['veiculo_id'] = $this->getOwnerRecord()->id;
        $data['criado_por'] = auth()->id();

        return $data;
    }
}
