<?php

namespace App\Filament\Promoter\Resources\Guests\Schemas;

use App\Enums\DocumentType;
use App\Rules\DocumentValidation;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evento e Setor')
                    ->description('Selecione onde o convidado será alocado')
                    ->schema([
                        Select::make('event_id')
                            ->label('Evento')
                            ->options(fn () => \App\Models\Event::whereIn('id', \App\Models\PromoterPermission::where('user_id', auth()->id())->pluck('event_id'))
                                ->pluck('name', 'id')
                            )
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('sector_id')
                            ->label('Setor')
                            ->options(fn (Get $get) => \App\Models\Sector::whereIn('id',
                                \App\Models\PromoterPermission::where('user_id', auth()->id())
                                    ->where('event_id', $get('event_id'))
                                    ->pluck('sector_id')
                            )->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(fn (Get $get) => ! $get('event_id')),

                        Placeholder::make('remaining')
                            ->label('Convites Restantes')
                            ->content(fn (Get $get) => (new \App\Services\GuestService)->canRegisterGuest(auth()->user(), (int) $get('event_id'), (int) $get('sector_id'))['remaining'] ?? 'Selecione o setor'
                            )
                            ->visible(fn (Get $get) => $get('sector_id')),
                    ])->columns(3),

                Section::make('Dados do Convidado')
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
                            ->helperText(fn (Get $get) => match ($get('document_type')) {
                                DocumentType::CPF, DocumentType::CPF->value => 'CPF: 11 dígitos numéricos',
                                DocumentType::RG, DocumentType::RG->value => 'RG: formato varia por estado',
                                DocumentType::PASSPORT, DocumentType::PASSPORT->value => 'Passaporte: letras e números',
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
            ]);
    }
}
