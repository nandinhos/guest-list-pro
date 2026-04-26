<?php

namespace App\Services;

use App\Enums\TipoVeiculo;
use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\Veiculo;

class ExcursoesImportService
{
    public array $parsedData = [];

    public array $preview = [];

    public array $previewSummary = [];

    public array $importResult = [
        'imported' => 0,
        'skipped_duplicates' => 0,
        'excursoes_created' => 0,
        'veiculos_created' => 0,
        'monitores_created' => 0,
        'orphan_veiculos' => 0,
        'errors' => [],
    ];

    public function parseFile(string $content): array
    {
        $parser = new ExcursoesListParser;
        $this->parsedData = $parser->parse($content);
        $this->preview = $this->parsedData;
        $this->previewSummary = $parser->report($this->parsedData);

        return $this->parsedData;
    }

    public function import(int $eventId, int $userId): array
    {
        $this->importResult = [
            'imported' => 0,
            'skipped_duplicates' => 0,
            'excursoes_created' => 0,
            'veiculos_created' => 0,
            'monitores_created' => 0,
            'orphan_veiculos' => 0,
            'errors' => [],
        ];

        $excursaoCache = [];

        foreach ($this->parsedData as $entry) {
            $excursaoId = null;

            if ($entry['excursao'] !== null) {
                $normalizedNome = mb_strtolower(trim($entry['excursao']));

                if (! isset($excursaoCache[$normalizedNome])) {
                    $excursao = Excursao::firstOrCreate(
                        ['event_id' => $eventId, 'nome' => $entry['excursao']],
                        ['criado_por' => $userId]
                    );
                    if ($excursao->wasRecentlyCreated) {
                        $this->importResult['excursoes_created']++;
                    }
                    $excursaoCache[$normalizedNome] = $excursao->id;
                }
                $excursaoId = $excursaoCache[$normalizedNome];
            }

            $veiculo = Veiculo::create([
                'excursao_id' => $excursaoId,
                'tipo' => $entry['vehicle_type']?->value ?? TipoVeiculo::ONIBUS->value,
                'placa' => $entry['vehicle_code'],
            ]);
            $this->importResult['veiculos_created']++;

            if ($excursaoId === null) {
                $this->importResult['orphan_veiculos']++;

                continue;
            }

            $exists = Monitor::where('event_id', $eventId)
                ->where('document_type', $entry['document_type']->value)
                ->where('document_number', $entry['document_number'])
                ->exists();

            if ($exists) {
                $this->importResult['skipped_duplicates']++;

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
            $this->importResult['monitores_created']++;
        }

        return $this->importResult;
    }
}
