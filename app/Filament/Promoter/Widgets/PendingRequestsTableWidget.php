<?php

namespace App\Filament\Promoter\Widgets;

use App\Models\ApprovalRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingRequestsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Solicitações em Aprovação';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ApprovalRequest::pending()
                    ->byRequester(auth()->id())
                    ->forEvent(session('selected_event_id'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Convidado')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Setor')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitado em')
                    ->dateTime('H:i')
                    ->color('gray'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhuma solicitação pendente')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
