<?php

namespace App\Filament\Resources\PromoterPermissions\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PromoterPermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.promoter-permissions.tables.columns.mobile_card')
                    ->label('Dados da Permissão')
                    ->hiddenFrom('md'),

                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('role')
                    ->label('Função')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => UserRole::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => UserRole::tryFrom($state)?->getColor() ?? 'gray')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('event.name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('sector.name')
                    ->label('Setor')
                    ->placeholder('-')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('guest_limit')
                    ->label('Limite')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('start_time')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem restrição')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('end_time')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem restrição')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
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
            ->actionsColumnLabel('Ações')
            ->recordActions([
                EditAction::make()
                    ->extraAttributes(['class' => 'hidden md:inline-flex']),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
