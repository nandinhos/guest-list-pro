<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditResource\Pages;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class AuditResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?string $label = 'Log de Auditoria';

    protected static ?string $pluralLabel = 'Logs de Auditoria';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Detalhes em readonly se necessário ver form
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Ocorrência')
                    ->formatStateUsing(fn ($state, $record) => view('filament.components.audit-occurrence', [
                        'date' => $state,
                        'user' => $record->causer?->name ?? 'Sistema',
                        'email' => $record->causer?->email,
                    ]))
                    ->html()
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('causer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('description')
                    ->label('Atividade')
                    ->formatStateUsing(function ($state, $record) {
                        // Log de sistema (sem subject) - exibe descrição direta
                        if (! $record->subject_type) {
                            return view('filament.components.audit-activity', [
                                'description' => $record->description,
                                'event' => $record->event ?? 'system',
                                'subject_type' => $record->description,
                                'subject_id' => null,
                                'entity_label' => 'Sistema',
                                'is_system_log' => true,
                            ]);
                        }

                        $subjectName = null;

                        // Tentar pegar do relacionamento
                        if ($record->subject) {
                            $subjectName = match (get_class($record->subject)) {
                                'App\Models\TicketSale' => $record->subject->buyer_name ?? 'Venda #'.$record->subject->id,
                                default => $record->subject->name ?? $record->subject->title ?? null,
                            };
                        }

                        // Fallback para properties (caso deletado)
                        if (! $subjectName) {
                            $attributes = $record->properties['attributes'] ?? [];
                            $old = $record->properties['old'] ?? [];
                            $subjectName = $attributes['name'] ?? $old['name'] ??
                                           $attributes['title'] ?? $old['title'] ??
                                           $attributes['buyer_name'] ?? $old['buyer_name'] ?? null;
                        }

                        $entityLabel = match ($record->subject_type) {
                            'App\Models\Guest' => 'Convidado',
                            'App\Models\Event' => 'Evento',
                            'App\Models\User' => 'Usuário',
                            'App\Models\TicketSale' => 'Venda',
                            default => class_basename($record->subject_type ?? 'Item'),
                        };

                        return view('filament.components.audit-activity', [
                            'description' => $record->description,
                            'event' => $record->event,
                            'subject_type' => $subjectName ?? $entityLabel,
                            'subject_id' => $record->subject_id,
                            'entity_label' => $entityLabel,
                            'is_system_log' => false,
                        ]);
                    })
                    ->html(),

                TextColumn::make('properties')
                    ->label('Alterações')
                    ->formatStateUsing(function ($state, $record) {
                        // Forçar uso do record->properties para garantir acesso correto ao JSON/Array
                        $properties = $record->properties;

                        // Se for uma Collection, converte para array
                        if ($properties instanceof \Illuminate\Support\Collection) {
                            $properties = $properties->toArray();
                        }

                        // Garante que é array
                        $properties = (array) $properties;

                        // Log de sistema (sem subject) - mostra as propriedades diretamente
                        if (! $record->subject_type) {
                            $keys = array_keys($properties);
                            if (empty($keys)) {
                                return 'Ação do sistema';
                            }
                            $count = count($keys);
                            $preview = implode(', ', array_slice($keys, 0, 3));

                            return $count > 3 ? "{$preview} e mais ".($count - 3) : $preview;
                        }

                        $attributes = $properties['attributes'] ?? [];
                        $old = $properties['old'] ?? [];

                        // Filtra chaves válidas
                        $keys = array_unique(array_merge(array_keys($attributes), array_keys($old)));

                        if (empty($keys)) {
                            return 'Sem alterações registradas';
                        }

                        $count = count($keys);
                        $preview = implode(', ', array_slice($keys, 0, 3));

                        return $count > 3 ? "{$preview} e mais ".($count - 3) : $preview;
                    })
                    ->color('gray')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Tipo de Entidade')
                    ->options([
                        'App\Models\Guest' => 'Convidado',
                        'App\Models\Event' => 'Evento',
                        'App\Models\User' => 'Usuário',
                        'App\Models\TicketSale' => 'Venda',
                    ]),
                SelectFilter::make('event')
                    ->label('Tipo de Ação')
                    ->options([
                        'created' => 'Criação',
                        'updated' => 'Atualização',
                        'deleted' => 'Remoção',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('De'),
                        DatePicker::make('created_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Auditoria')
                    ->modalContent(fn ($record) => view('filament.components.audit-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Fechar')),
            ])
            ->bulkActions([])
            ->recordAction('view')
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudits::route('/'),
        ];
    }
}
