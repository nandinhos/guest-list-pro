<?php

namespace App\Filament\Resources\PromoterPermissions\Tables;

use App\Enums\UserRole;
use App\Models\Guest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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

                TextColumn::make('guest_count')
                    ->label('Usados')
                    ->getStateUsing(fn ($record) => Guest::where('promoter_id', $record->user_id)
                        ->where('event_id', $record->event_id)
                        ->where('sector_id', $record->sector_id)
                        ->count())
                    ->sortable(),

                TextColumn::make('guest_limit')
                    ->label('Limite')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('remaining')
                    ->label('Restantes')
                    ->getStateUsing(fn ($record) => max(0, $record->guest_limit - Guest::where('promoter_id', $record->user_id)
                        ->where('event_id', $record->event_id)
                        ->where('sector_id', $record->sector_id)
                        ->count()))
                    ->color(fn (int $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

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
                Action::make('quickEdit')
                    ->label('Editar Cota')
                    ->icon('heroicon-m-pencil-square')
                    ->color('info')
                    ->slideOver()
                    ->form([
                        \Filament\Forms\Components\TextInput::make('guest_limit')
                            ->label('Limite de Convidados')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['guest_limit' => $data['guest_limit']]);
                        Notification::make()
                            ->title('Cota atualizada')
                            ->body("Limite alterado para {$data['guest_limit']}")
                            ->success()
                            ->send();
                    })
                    ->fillForm(fn ($record): array => ['guest_limit' => $record->guest_limit]),

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
