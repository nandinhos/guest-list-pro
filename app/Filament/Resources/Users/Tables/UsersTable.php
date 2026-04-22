<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                ViewColumn::make('mobile_card')
                    ->view('filament.resources.users.tables.columns.mobile_card')
                    ->label('Dados do Usuário')
                    ->getStateUsing(fn ($record) => $record)
                    ->hiddenFrom('md'),

                TextColumn::make('name')
                    ->label('Usuário')
                    ->description(fn ($record) => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('bold')
                    ->visibleFrom('md'),

                TextColumn::make('role')
                    ->label('Perfil / Acesso')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value) {
                        'admin' => 'danger',
                        'promoter' => 'primary',
                        'validator' => 'success',
                        'excursionista' => 'teal',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state?->value) {
                        'admin' => 'heroicon-m-shield-check',
                        'promoter' => 'heroicon-m-megaphone',
                        'validator' => 'heroicon-m-ticket',
                        'excursionista' => 'heroicon-m-user-group',
                        default => 'heroicon-m-user',
                    })
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                    ->formatStateUsing(fn ($state) => $state ? 'ATIVO' : 'INATIVO')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Membro desde')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),

                TextColumn::make('updated_at')
                    ->label('Última atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
