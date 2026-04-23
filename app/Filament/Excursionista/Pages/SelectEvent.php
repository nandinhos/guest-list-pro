<?php

namespace App\Filament\Excursionista\Pages;

use App\Filament\Pages\SelectEventBase;

class SelectEvent extends SelectEventBase
{
    public static function getPanelId(): string
    {
        return 'excursionista';
    }
}
