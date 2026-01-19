<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
use Filament\Actions\CreateAction;
use Filament\Helpers\Exports\Export;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('exportCsv')
                ->label('Exportar Todos (CSV)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $records = \App\Models\Guest::with(['event', 'sector', 'promoter', 'validator'])->get();

                    return response()->streamDownload(function () use ($records) {
                        $file = fopen('php://output', 'w');
                        fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM
                        fputcsv($file, ['ID', 'Evento', 'Setor', 'Nome', 'Documento', 'Email', 'Status', 'Promoter', 'Validado Por', 'Data Check-in'], ';');

                        foreach ($records as $record) {
                            fputcsv($file, [
                                $record->id,
                                $record->event?->name,
                                $record->sector?->name,
                                $record->name,
                                $record->document,
                                $record->email,
                                $record->is_checked_in ? 'Sim' : 'Não',
                                $record->promoter?->name,
                                $record->validator?->name,
                                $record->checked_in_at?->format('d/m/Y H:i'),
                            ], ';');
                        }
                        fclose($file);
                    }, 'todos-convidados-'.now()->format('d-m-Y').'.csv');
                }),
        ];
    }
}
