<?php

namespace App\Console\Commands;

use App\Models\Guest;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NormalizeGuestNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guests:normalize {--chunk=500 : Quantidade de registros por lote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normaliza os nomes e documentos de todos os convidados existentes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando normalização de convidados...');

        $chunkSize = (int) $this->option('chunk');
        $total = Guest::count();
        $processed = 0;

        $this->withProgressBar($total, function () use ($chunkSize, &$processed) {
            Guest::query()
                ->whereNull('name_normalized')
                ->orWhereNull('document_normalized')
                ->chunkById($chunkSize, function ($guests) use (&$processed) {
                    foreach ($guests as $guest) {
                        $guest->name_normalized = strtolower(Str::ascii($guest->name ?? ''));
                        $guest->document_normalized = preg_replace('/\D/', '', $guest->document ?? '');
                        $guest->saveQuietly(); // Evita triggerar observer novamente
                        $processed++;
                    }
                });
        });

        $this->newLine();
        $this->info("Normalização concluída! {$processed} registros atualizados.");

        return Command::SUCCESS;
    }
}
