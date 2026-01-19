<?php

namespace App\Filament\Resources\PromoterPermissions\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PromoterPermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Função')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => UserRole::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => UserRole::tryFrom($state)?->getColor() ?? 'gray')
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('guest_limit')
                    ->label('Limite')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem restrição')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem restrição')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Função')
                    ->options([
                        UserRole::PROMOTER->value => UserRole::PROMOTER->getLabel(),
                        UserRole::VALIDATOR->value => UserRole::VALIDATOR->getLabel(),
                        UserRole::BILHETERIA->value => UserRole::BILHETERIA->getLabel(),
                    ]),

                SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
