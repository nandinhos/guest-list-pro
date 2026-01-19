<?php

namespace App\Filament\Promoter\Pages;

use App\Filament\Pages\SelectEventBase;

class SelectEvent extends SelectEventBase
{
    public static function getPanelId(): string
    {
        return 'promoter';
    }
}
