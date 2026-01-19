<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

abstract class SelectEventBase extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $title = 'Selecionar Evento';

    protected string $view = 'filament.pages.select-event';

    protected static string $layout = 'layouts.fullscreen';

    public function getMaxWidth(): \Filament\Support\Enums\MaxWidth|string|null
    {
        return \Filament\Support\Enums\MaxWidth::SevenExtraLarge;
    }

    /**
     * Retorna o ID do painel atual.
     */
    abstract public static function getPanelId(): string;

    public static function getNavigationLabel(): string
    {
        return 'Selecionar Evento';
    }
}
