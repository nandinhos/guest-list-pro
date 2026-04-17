<?php

namespace App\Filament\Promoter\Resources\Guests\Schemas;

use App\Enums\DocumentType;
use App\Models\User;
use App\Rules\DocumentValidation;
use App\Services\GuestValidationService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        $userId = auth()->id();

        return $schema
            ->components([
                Section::make('Localização')
                    ->description('Selecione o evento e setor')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            Select::make('event_id')
                                ->label('Evento')
                                ->options(fn () => \App\Models\Event::whereIn('id', \App\Models\PromoterPermission::where('user_id', $userId)->pluck('event_id'))
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
                                    \App\Models\PromoterPermission::where('user_id', $userId)
                                        ->where('event_id', $get('event_id'))
                                        ->pluck('sector_id')
                                )->pluck('name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->disabled(fn (Get $get) => ! $get('event_id')),

                            Placeholder::make('quota_info')
                                ->label('Cota')
                                ->content(function (Get $get) use ($userId) {
                                    if (! $get('sector_id')) {
                                        return 'Selecione o setor';
                                    }

                                    $user = User::find($userId);

                                    if (! $user) {
                                        return 'Erro ao carregar';
                                    }

                                    $validation = GuestValidationService::for(
                                        $user,
                                        (int) $get('event_id'),
                                        (int) $get('sector_id')
                                    );

                                    $summary = $validation->getSummary();

                                    $guestsRemaining = $summary['guests_remaining'];
                                    $companionsRemaining = $summary['companions_remaining'];
                                    $plusOne = $summary['plus_one_enabled'];

                                    $color = $guestsRemaining > 10 ? 'success' : ($guestsRemaining > 0 ? 'warning' : 'danger');

                                    $text = "{$guestsRemaining} convite(s) disponível(eis)";
                                    if ($plusOne) {
                                        $text .= " + {$companionsRemaining} acompanhante(s)";
                                    }

                                    return new \Illuminate\Support\HtmlString("<span class='text-{$color}-500'>{$text}</span>");
                                })
                                ->visible(fn (Get $get) => $get('sector_id')),
                        ]),
                    ]),

                Section::make('Dados do Convidado')
                    ->description('Informe os dados do convidado')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'lg' => 4,
                        ])->schema([
                            TextInput::make('name')
                                ->label('Nome Completo')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Nome como no documento'),

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
                                ->maxLength(255)
                                ->placeholder('email@exemplo.com (opcional)'),
                        ]),
                    ]),
            ]);
    }
}
