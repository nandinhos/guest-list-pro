<?php

namespace App\Filament\Resources\Excursionistas;

use App\Enums\UserRole;
use App\Filament\Resources\Excursionistas\Pages\CreateExcursionista;
use App\Filament\Resources\Excursionistas\Pages\EditExcursionista;
use App\Filament\Resources\Excursionistas\Pages\ListExcursionistas;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ExcursionistaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Excursionista';

    protected static ?string $pluralModelLabel = 'Excursionistas';

    protected static ?string $navigationLabel = 'Excursionistas';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'ATIVO' : 'INATIVO')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('role', UserRole::EXCURSIONISTA);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExcursionistas::route('/'),
            'create' => CreateExcursionista::route('/create'),
            'edit' => EditExcursionista::route('/{record}/edit'),
        ];
    }
}
