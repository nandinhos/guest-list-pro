<?php

namespace App\Filament\Resources\ApprovalRequests;

use App\Filament\Resources\ApprovalRequests\Tables\ApprovalRequestsTable;
use App\Models\ApprovalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ApprovalRequestResource extends Resource
{
    protected static ?string $model = ApprovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static UnitEnum|string|null $navigationGroup = 'Gestão';

    protected static ?string $label = 'Solicitação';

    protected static ?string $pluralLabel = 'Solicitações';

    protected static ?int $navigationSort = 1;

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
        return ApprovalRequestsTable::make($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ApprovalRequest::pending()->count();

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
            ? '1 solicitação pendente'
            : "{$count} solicitações pendentes";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalRequests::route('/'),
            'report' => Pages\RequestsReport::route('/report'),
        ];
    }
}
