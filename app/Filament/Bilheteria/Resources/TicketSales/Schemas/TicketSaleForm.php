<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Schemas;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Models\Sector;
use App\Models\TicketType;
use App\Rules\DocumentValidation;
use App\Services\TicketSaleService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TicketSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Comprador')
                    ->description('Informações do comprador do ingresso')
                    ->columns(3)
                    ->schema([
                        TextInput::make('buyer_name')
                            ->label('Nome do Comprador')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nome completo'),

                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(DocumentType::class)
                            ->default(DocumentType::CPF->value)
                            ->live()
                            ->native(false),

                        TextInput::make('buyer_document')
                            ->label('Documento')
                            ->required()
                            ->maxLength(20)
                            ->placeholder(fn (Get $get) => ($type = $get('document_type')) instanceof DocumentType ? $type->getPlaceholder() : (DocumentType::tryFrom($type ?? '')?->getPlaceholder() ?? 'CPF, RG ou Passaporte'))
                            ->helperText(fn (Get $get) => match ($get('document_type') instanceof DocumentType ? $get('document_type')->value : $get('document_type')) {
                                DocumentType::CPF->value => 'CPF: 11 dígitos numéricos',
                                DocumentType::RG->value => 'RG: formato varia por estado',
                                DocumentType::PASSPORT->value => 'Passaporte: letras e números',
                                default => null,
                            })
                            ->rules([
                                fn (Get $get): DocumentValidation => new DocumentValidation(
                                    type: ($type = $get('document_type')) instanceof DocumentType ? $type : DocumentType::tryFrom($type ?? ''),
                                    allowEmpty: false
                                ),
                            ]),
                    ]),

                Section::make('Dados do Ingresso')
                    ->description('Selecione o setor e o tipo de ingresso')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            Select::make('sector_id')
                                ->label('Setor')
                                ->options(fn () => Sector::query()
                                    ->where('event_id', session('selected_event_id'))
                                    ->pluck('name', 'id'))
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $set('value', 0);
                                    $set('ticket_type_id', null);
                                })
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('ticket_type_id')
                                ->label('Tipo de Ingresso')
                                ->options(function (Get $get) {
                                    $sectorId = $get('sector_id');
                                    $eventId = session('selected_event_id');

                                    if (! $sectorId) {
                                        return [];
                                    }

                                    return TicketType::query()
                                        ->where('event_id', $eventId)
                                        ->where('is_active', true)
                                        ->where('is_visible', true)
                                        ->whereHas('sectorPrices', function ($query) use ($sectorId) {
                                            $query->where('sector_id', $sectorId);
                                        })
                                        ->pluck('name', 'id');
                                })
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    if ($get('use_custom_price')) {
                                        return;
                                    }

                                    $ticketType = TicketType::find($get('ticket_type_id'));
                                    $sectorId = $get('sector_id');

                                    if (! $ticketType || ! $sectorId) {
                                        return;
                                    }

                                    try {
                                        $set('value', TicketSaleService::getPriceForSector($ticketType, $sectorId));
                                    } catch (\RuntimeException $e) {
                                        $set('value', 0);
                                    }
                                })
                                ->live()
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),

                        Placeholder::make('ticket_price')
                            ->label('Valor')
                            /**
                             * @see P2 (DEVORQ review 2026-04-21): FALSE POSITIVE em code review.
                             * Dois TicketType::find() separados (aqui e no hint abaixo)
                             * sao callbacks Livewire independentes - nao sao N+1.
                             * Disparam apenas quando o usuario seleciona um tipo (acao manual).
                             * Isolar em variavel adicionaria complexidade sem beneficio.
                             */
                            ->content(function (Get $get) {
                                $ticketType = TicketType::find($get('ticket_type_id'));
                                $sectorId = $get('sector_id');

                                if (! $ticketType || ! $sectorId) {
                                    return 'Selecione o setor e o tipo';
                                }

                                try {
                                    $price = TicketSaleService::getPriceForSector($ticketType, $sectorId);
                                    $sector = Sector::find($sectorId);

                                    return 'R$ '.number_format($price, 2, ',', '.').' ('.$sector->name.')';
                                } catch (\RuntimeException $e) {
                                    return 'Preço não configurado';
                                }
                            })
                            /**
                             * @see P2 (DEVORQ review 2026-04-21): FALSE POSITIVE.
                             * @see comentario acima em ->content().
                             */
                            ->hint(function (Get $get) {
                                $ticketType = TicketType::find($get('ticket_type_id'));

                                return $ticketType?->description;
                            }),
                    ]),

                Section::make('Pagamento')
                    ->description('Forma de pagamento')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            Select::make('payment_method')
                                ->label('Forma de Pagamento')
                                ->options(PaymentMethod::class)
                                ->required()
                                ->native(false),

                            TextInput::make('value')
                                ->label('Valor Cobrado')
                                ->numeric()
                                ->prefix('R$')
                                ->required()
                                ->disabled(fn (Get $get) => ! $get('use_custom_price'))
                                ->hint(fn (Get $get) => $get('use_custom_price') ? 'Digite o valor personalizado' : 'Valor automático')
                                ->default(function (Get $get) {
                                    if ($get('use_custom_price')) {
                                        return null;
                                    }

                                    $ticketType = TicketType::find($get('ticket_type_id'));
                                    $sectorId = $get('sector_id');

                                    if (! $ticketType || ! $sectorId) {
                                        return 0;
                                    }

                                    try {
                                        return TicketSaleService::getPriceForSector($ticketType, $sectorId);
                                    } catch (\RuntimeException $e) {
                                        return 0;
                                    }
                                }),

                            Toggle::make('use_custom_price')
                                ->label('Usar valor personalizado')
                                ->default(false)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    if ($get('use_custom_price')) {
                                        $set('value', null);
                                    } else {
                                        $ticketType = TicketType::find($get('ticket_type_id'));
                                        $sectorId = $get('sector_id');

                                        if (! $ticketType || ! $sectorId) {
                                            return;
                                        }

                                        try {
                                            $set('value', TicketSaleService::getPriceForSector($ticketType, $sectorId));
                                        } catch (\RuntimeException $e) {
                                            $set('value', 0);
                                        }
                                    }
                                }),
                        ]),
                    ]),

                Section::make('Observações')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->placeholder('Observações adicionais sobre a venda'),
                    ]),
            ]);
    }
}
