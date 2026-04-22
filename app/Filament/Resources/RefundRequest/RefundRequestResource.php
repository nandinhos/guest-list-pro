<?php

namespace App\Filament\Resources\RefundRequest;

use App\Filament\Resources\RefundRequest\Pages\ListRefundRequests;
use App\Models\RefundRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static UnitEnum|string|null $navigationGroup = 'Gestão';

    protected static ?string $label = 'Estorno';

    protected static ?string $pluralLabel = 'Estornos';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return RefundRequestsTable::make($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = RefundRequest::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = (int) static::getNavigationBadge();

        if ($count >= 10) {
            return 'danger';
        }

        if ($count >= 5) {
            return 'warning';
        }

        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = (int) static::getNavigationBadge();

        if ($count === 0) {
            return null;
        }

        return $count === 1
            ? '1 estorno pendente'
            : "{$count} estornos pendentes";
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefundRequests::route('/'),
        ];
    }
}