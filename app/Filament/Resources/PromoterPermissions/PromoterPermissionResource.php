<?php

namespace App\Filament\Resources\PromoterPermissions;

use App\Filament\Resources\PromoterPermissions\Pages\CreatePromoterPermission;
use App\Filament\Resources\PromoterPermissions\Pages\EditPromoterPermission;
use App\Filament\Resources\PromoterPermissions\Pages\ListPromoterPermissions;
use App\Filament\Resources\PromoterPermissions\Schemas\PromoterPermissionForm;
use App\Filament\Resources\PromoterPermissions\Tables\PromoterPermissionsTable;
use App\Models\PromoterPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromoterPermissionResource extends Resource
{
    protected static ?string $model = PromoterPermission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Permissão';

    protected static ?string $pluralModelLabel = 'Permissões';

    public static function form(Schema $schema): Schema
    {
        return PromoterPermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromoterPermissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromoterPermissions::route('/'),
            'create' => CreatePromoterPermission::route('/create'),
            'edit' => EditPromoterPermission::route('/{record}/edit'),
        ];
    }
}
