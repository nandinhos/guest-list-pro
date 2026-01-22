<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.users.tables.columns.mobile_card')
                    ->label('Dados do Usuário')
                    ->hiddenFrom('md'),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('role')
                    ->label('Perfil')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('is_active')
                    ->label('Ativo')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Ativo' : 'Inativo')
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
                //
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
