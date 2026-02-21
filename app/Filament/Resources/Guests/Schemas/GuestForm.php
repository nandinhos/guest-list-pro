<?php

namespace App\Filament\Resources\Guests\Schemas;

use App\Enums\DocumentType;
use App\Rules\DocumentValidation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Evento e Localização')
                    ->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->relationship('event', 'name')
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('sector_id')
                            ->label('Setor')
                            ->relationship(
                                name: 'sector',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, Get $get) => $query->where('event_id', $get('event_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(fn (Get $get) => ! $get('event_id')),

                        Select::make('promoter_id')
                            ->label('Promoter Responsável')
                            ->relationship(
                                name: 'promoter',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('role', \App\Enums\UserRole::PROMOTER->value)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Dados do Convidado')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),

                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(DocumentType::class)
                            ->default(DocumentType::CPF->value)
                            ->live()
                            ->native(false),

                        TextInput::make('document')
                            ->label('Documento')
                            ->placeholder(fn (Get $get) => ($type = $get('document_type')) instanceof DocumentType ? $type->getPlaceholder() : (DocumentType::tryFrom($type ?? '')?->getPlaceholder() ?? 'Digite o documento'))
                            ->helperText(fn (Get $get) => match ($get('document_type') instanceof DocumentType ? $get('document_type')->value : $get('document_type')) {
                                DocumentType::CPF->value => 'CPF: 11 dígitos numéricos',
                                DocumentType::RG->value => 'RG: formato varia por estado',
                                DocumentType::PASSPORT->value => 'Passaporte: letras e números',
                                default => null,
                            })
                            ->rules([
                                fn (Get $get): DocumentValidation => new DocumentValidation(
                                    type: ($type = $get('document_type')) instanceof DocumentType ? $type : DocumentType::tryFrom($type ?? ''),
                                    allowEmpty: true
                                ),
                            ])
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                    ])->columns(4),

                \Filament\Schemas\Components\Section::make('Status de Check-in')
                    ->description('Estes campos são atualizados automaticamente durante o evento')
                    ->schema([
                        Toggle::make('is_checked_in')
                            ->label('Já realizou Check-in?')
                            ->default(false),
                        DateTimePicker::make('checked_in_at')
                            ->label('Horário do Check-in')
                            ->native(false),
                        Select::make('checked_in_by')
                            ->label('Validado por')
                            ->relationship('validator', 'name')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }
}
