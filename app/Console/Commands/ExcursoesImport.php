<?php

namespace App\Console\Commands;

use App\Enums\TipoVeiculo;
use App\Models\Event;
use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\User;
use App\Services\ExcursoesListParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExcursoesImport extends Command
{
    protected $signature = 'excursoes:import
        {file : Caminho do arquivo .md com a lista de excursões}
        {--event= : ID do evento para importar}
        {--import : Executar importação (padrão: somente análise)}
        {--user= : ID do usuário responsável (padrão: primeiro admin)}';

    protected $description = 'Analisa e importa lista de excursões a partir de um arquivo .md';

    public function handle(ExcursoesListParser $parser): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");

            return self::FAILURE;
        }

        $raw = file_get_contents($file);
        $entries = $parser->parse($raw);
        $report = $parser->report($entries);

        $this->printReport($report, $entries);

        if (! $this->option('import')) {
            $this->newLine();
            $this->line('Para importar, execute com <comment>--import --event=ID</comment>');

            return self::SUCCESS;
        }

        $eventId = $this->option('event');
        if (! $eventId) {
            $this->error('O parâmetro --event=ID é obrigatório para importação.');

            return self::FAILURE;
        }

        $event = Event::find($eventId);
        if (! $event) {
            $this->error("Evento #{$eventId} não encontrado.");

            return self::FAILURE;
        }

        $userId = $this->option('user') ?? User::where('role', 'admin')->value('id');
        if (! $userId) {
            $this->error('Nenhum usuário admin encontrado. Use --user=ID.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Importando para: <comment>{$event->name}</comment>");
        $this->newLine();

        $this->importEntries($entries, (int) $eventId, (int) $userId);

        return self::SUCCESS;
    }

    private function printReport(array $report, array $entries): void
    {
        $this->newLine();
        $this->line('<fg=cyan>===== ANÁLISE DO ARQUIVO =====</>');
        $this->newLine();

        $this->line("  Total de entradas (após dedup por documento): <comment>{$report['total_entries']}</comment>");
        $this->newLine();

        $this->line("  <fg=yellow>Excursões distintas:</> {$report['excursoes_total']}");
        foreach ($report['excursoes'] as $nome) {
            $this->line("    · {$nome}");
        }
        $this->newLine();

        $this->line('  <fg=yellow>Veículos por tipo:</>');
        foreach ($report['vehicles_by_type'] as $tipo => $count) {
            $label = TipoVeiculo::from($tipo)->label();
            $this->line("    · {$label}: {$count}");
        }
        if ($report['vehicles_no_type'] > 0) {
            $this->line("    · Sem tipo: {$report['vehicles_no_type']}");
        }
        $this->newLine();

        $this->line("  <fg=yellow>Total de monitores:</> {$report['monitors_total']}");
        $this->newLine();

        // Show entries without excursão name (potential parsing issues)
        $noExcursao = array_filter($entries, fn ($e) => $e['excursao'] === null);
        if (count($noExcursao) > 0) {
            $this->warn('  Entradas sem nome de excursão: '.count($noExcursao));
            foreach (array_slice(array_values($noExcursao), 0, 5) as $e) {
                $this->line("    · Monitor: {$e['monitor']} | Doc: {$e['document_number']}");
            }
            if (count($noExcursao) > 5) {
                $this->line('    · ... e mais '.(count($noExcursao) - 5).' entradas');
            }
        }
    }

    private function importEntries(array $entries, int $eventId, int $userId): void
    {
        $createdExcursoes = 0;
        $createdVeiculos = 0;
        $createdMonitores = 0;
        $skippedDuplicates = 0;
        $createdNoExcursao = 0;

        $excursaoCache = [];

        $bar = $this->output->createProgressBar(count($entries));
        $bar->start();

        DB::transaction(function () use (
            $entries, $eventId, $userId,
            &$createdExcursoes, &$createdVeiculos, &$createdMonitores,
            &$skippedDuplicates, &$createdNoExcursao,
            &$excursaoCache, $bar
        ) {
            foreach ($entries as $entry) {
                $excursaoId = null;

                if ($entry['excursao'] !== null) {
                    $normalizedNome = mb_strtolower(trim($entry['excursao']));
                    if (! isset($excursaoCache[$normalizedNome])) {
                        $excursao = Excursao::firstOrCreate(
                            ['event_id' => $eventId, 'nome' => $entry['excursao']],
                            ['criado_por' => $userId]
                        );
                        if ($excursao->wasRecentlyCreated) {
                            $createdExcursoes++;
                        }
                        $excursaoCache[$normalizedNome] = $excursao->id;
                    }
                    $excursaoId = $excursaoCache[$normalizedNome];
                }

                $veiculo = \App\Models\Veiculo::create([
                    'excursao_id' => $excursaoId,
                    'tipo' => $entry['vehicle_type']?->value ?? TipoVeiculo::ONIBUS->value,
                    'placa' => $entry['vehicle_code'],
                ]);
                $createdVeiculos++;

                if ($excursaoId === null) {
                    $createdNoExcursao++;
                    $bar->advance();

                    continue;
                }

                $exists = Monitor::where('event_id', $eventId)
                    ->where('document_type', $entry['document_type']->value)
                    ->where('document_number', $entry['document_number'])
                    ->exists();

                if ($exists) {
                    $skippedDuplicates++;
                    $bar->advance();

                    continue;
                }

                Monitor::create([
                    'veiculo_id' => $veiculo->id,
                    'event_id' => $eventId,
                    'nome' => $entry['monitor'] ?? 'Sem nome',
                    'document_type' => $entry['document_type']->value,
                    'document_number' => $entry['document_number'],
                    'criado_por' => $userId,
                ]);
                $createdMonitores++;

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("  Excursões criadas:  {$createdExcursoes}");
        $this->info("  Veículos criados:   {$createdVeiculos}");
        $this->info("  Monitores criados:  {$createdMonitores}");

        if ($skippedDuplicates > 0) {
            $this->warn("  Monitores ignorados (duplicata): {$skippedDuplicates}");
        }
        if ($createdNoExcursao > 0) {
            $this->info("  Veículos sem excursão ( orphan): {$createdNoExcursao}");
        }

        $this->newLine();
        $this->info('Importação concluída.');
    }
}
