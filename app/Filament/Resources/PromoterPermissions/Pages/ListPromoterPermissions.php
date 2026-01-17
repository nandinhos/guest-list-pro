<?php

namespace App\Filament\Resources\PromoterPermissions\Pages;

use App\Filament\Resources\PromoterPermissions\PromoterPermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromoterPermissions extends ListRecords
{
    protected static string $resource = PromoterPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
