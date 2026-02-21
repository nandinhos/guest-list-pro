<?php

namespace App\Traits;

use App\Models\Guest;
use App\Models\Sector;
use Filament\Actions\Action;
use Illuminate\Support\Str;

trait HasGuestImport
{
    /**
     * Retorna o preview do texto parseado.
     */
    public function getParsedPreviewProperty(): array
    {
        if (empty($this->textContent)) {
            return [];
        }

        $lines = $this->parseText($this->textContent, $this->delimiter);

        return array_slice($lines, 0, 20); // Limita preview a 20 linhas
    }

    /**
     * Parseia o texto baseado no delimitador selecionado.
     * Inclui lógica amigável para detectar vírgulas no modo newline.
     */
    protected function parseText(string $text, string $delimiter): array
    {
        $lines = explode("\n", trim($text));
        $results = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = match ($delimiter) {
                'comma' => array_map('trim', explode(',', $line)),
                'semicolon' => array_map('trim', explode(';', $line)),
                'tab' => array_map('trim', explode("\t", $line)),
                'pipe' => array_map('trim', explode('|', $line)),
                default => [$line], // newline = um campo por linha (só nome)
            };

            // Lógica amigável: se for newline mas o usuário colou com vírgula, tenta extrair
            if ($delimiter === 'newline' && str_contains($line, ',')) {
                $parts = array_map('trim', explode(',', $line));
            }

            $name = $parts[0] ?? '';
            $document = $parts[1] ?? '';

            if (! empty($name)) {
                $results[] = [
                    'line' => $index + 1,
                    'name' => $name,
                    'document' => $document,
                    'valid' => true,
                ];
            }
        }

        return $results;
    }

    /**
     * Ação de baixar o template de importação.
     */
    public function downloadTemplateAction(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Baixar Modelo')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(function () {
                $path = storage_path('app/templates/modelo-importacao-convidados.xlsx');
                
                if (!file_exists($path)) {
                    \Filament\Notifications\Notification::make()
                        ->title('Erro')
                        ->body('Arquivo de modelo não encontrado.')
                        ->danger()
                        ->send();
                    return;
                }

                return response()->download($path);
            });
    }
}
