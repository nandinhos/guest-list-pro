<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Schemas;

use App\Enums\PaymentMethod;
use App\Models\Event;
use App\Models\Sector;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Comprador')
                    ->description('Informações do comprador do ingresso')
                    ->columns(2)
                    ->schema([
                        TextInput::make('buyer_name')
                            ->label('Nome do Comprador')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nome completo'),

                        TextInput::make('buyer_document')
                            ->label('Documento')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('CPF ou RG'),
                    ]),

                Section::make('Dados do Ingresso')
                    ->description('Informações do ingresso a ser emitido')
                    ->columns(2)
                    ->schema([
                        Select::make('sector_id')
                            ->label('Setor')
                            ->options(fn () => Sector::query()
                                ->where('event_id', session('selected_event_id'))
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Placeholder::make('ticket_price')
                            ->label('Valor do Ingresso')
                            ->content(function () {
                                $event = Event::find(session('selected_event_id'));

                                return $event?->ticket_price
                                    ? 'R$ '.number_format($event->ticket_price, 2, ',', '.')
                                    : 'Não definido';
                            }),
                    ]),

                Section::make('Pagamento')
                    ->description('Forma de pagamento')
                    ->columns(2)
                    ->schema([
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
                            ->default(fn () => Event::find(session('selected_event_id'))?->ticket_price),
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
