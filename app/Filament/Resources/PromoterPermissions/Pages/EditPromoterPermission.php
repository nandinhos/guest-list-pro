<?php

namespace App\Filament\Resources\PromoterPermissions\Pages;

use App\Filament\Resources\PromoterPermissions\PromoterPermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromoterPermission extends EditRecord
{
    protected static string $resource = PromoterPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
