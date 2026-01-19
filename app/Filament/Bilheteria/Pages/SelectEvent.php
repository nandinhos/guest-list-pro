<?php

namespace App\Filament\Bilheteria\Pages;

use App\Filament\Pages\SelectEventBase;

class SelectEvent extends SelectEventBase
{
    public static function getPanelId(): string
    {
        return 'bilheteria';
    }
}
