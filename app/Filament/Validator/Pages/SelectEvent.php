<?php

namespace App\Filament\Validator\Pages;

use App\Filament\Pages\SelectEventBase;

class SelectEvent extends SelectEventBase
{
    public static function getPanelId(): string
    {
        return 'validator';
    }
}
