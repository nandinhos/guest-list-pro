<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuestImportService
{
    public array $parsedData = [];

    public array $importResult = [
        'imported' => 0,
        'duplicates' => 0,
        'errors' => [],
        'warnings' => [],
    ];

    public array $preview = [];

    public function parseFile(string $content): array
    {
        $this->parsedData = [];
        $this->preview = [];

        $lines = explode("\n", $content);
        $currentPromoter = null;
        $currentSector = null;

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if (preg_match('/^#{1,3}\s*Convidados\s+(.+?)\s*#{1,3}$/i', $line, $matches)) {
                $currentPromoter = trim($matches[1]);
                $currentSector = null;
                continue;
            }

            if (preg_match('/^#{1,3}\s*(BACKSTAGE|PISTA)\s*#{1,3}$/i', $line, $matches)) {
                $currentSector = strtoupper(trim($matches[1]));
                continue;
            }

            if ($currentPromoter === null || $currentSector === null) {
                continue;
            }

            if (preg_match('/^(.+?),\s*(.+)$/', $line, $matches)) {
                $name = trim($matches[1]);
                $document = trim($matches[2]);

                $this->parsedData[] = [
                    'promoter_name' => $currentPromoter,
                    'sector_name' => $currentSector,
                    'name' => $name,
                    'document' => $document,
                ];

                $this->preview[] = [
                    'promoter' => $currentPromoter,
                    'sector' => $currentSector,
                    'name' => $name,
                    'document' => $document,
                ];
            }
        }

        return $this->parsedData;
    }

    public function import(int $eventId, int $adminUserId): array
    {
        $this->importResult = [
            'imported' => 0,
            'duplicates' => 0,
            'errors' => [],
            'promoters_created' => 0,
        ];

        $event = Event::findOrFail($eventId);
        $sectors = $this->getSectorsByEvent($eventId);

        $promoterCache = $this->getPromoterCache($eventId);

        DB::beginTransaction();

        try {
            foreach ($this->parsedData as $item) {
                $result = $this->importGuest($event, $sectors, $promoterCache, $item, $adminUserId);

                if ($result === 'imported') {
                    $this->importResult['imported']++;
                } elseif ($result === 'duplicate') {
                    $this->importResult['duplicates']++;
                } else {
                    $this->importResult['errors'][] = $result;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->importResult['errors'][] = 'Erro_transaction: ' . $e->getMessage();
        }

        return $this->importResult;
    }

    protected function importGuest(
        Event $event,
        array $sectors,
        array &$promoterCache,
        array $item,
        int $adminUserId
    ): string {
        $sectorName = $item['sector_name'];
        $sector = $sectors[$sectorName] ?? null;

        if (! $sector) {
            return "Setor {$sectorName} não encontrado para o evento";
        }

        $document = preg_replace('/[^0-9Xx]/', '', $item['document']);
        $documentType = strlen($document) > 11 ? DocumentType::PASSPORT : DocumentType::CPF;

        $existingGuest = Guest::where('event_id', $event->id)
            ->where('document', $document)
            ->first();

        if ($existingGuest) {
            $this->importResult['warnings'][] = [
                'name' => $item['name'],
                'document' => $item['document'],
                'reason' => 'CPF já cadastrado',
            ];
            return 'duplicate';
        }

        $promoterName = $item['promoter_name'];

        if (! isset($promoterCache[$promoterName])) {
            $promoter = User::where('name', $promoterName)
                ->where('role', 'promoter')
                ->first();

            if (! $promoter) {
                $promoter = User::create([
                    'name' => $promoterName,
                    'email' => strtolower(str_replace(' ', '.', $promoterName)) . '@imported.local',
                    'password' => bcrypt(bin2hex(random_bytes(8))),
                    'role' => 'promoter',
                    'is_active' => true,
                ]);
                $this->importResult['promoters_created']++;
            }

            $promoterCache[$promoterName] = $promoter->id;
        }

        $promoterId = $promoterCache[$promoterName];

        Guest::create([
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'promoter_id' => $promoterId,
            'name' => $item['name'],
            'document' => $document,
            'document_type' => $documentType,
            'email' => null,
        ]);

        return 'imported';
    }

    protected function getSectorsByEvent(int $eventId): array
    {
        return Sector::where('event_id', $eventId)
            ->get()
            ->mapWithKeys(fn ($sector) => [strtoupper($sector->name) => $sector])
            ->toArray();
    }

    protected function getPromoterCache(int $eventId): array
    {
        return User::where('role', 'promoter')
            ->get()
            ->mapWithKeys(fn ($user) => [$user->name => $user->id])
            ->toArray();
    }

    public function getPreviewSummary(): array
    {
        $summary = [
            'total' => count($this->preview),
            'by_promoter' => [],
            'by_sector' => [],
        ];

        foreach ($this->preview as $item) {
            $promoter = $item['promoter'];
            $sector = $item['sector'];

            if (! isset($summary['by_promoter'][$promoter])) {
                $summary['by_promoter'][$promoter] = 0;
            }
            $summary['by_promoter'][$promoter]++;

            if (! isset($summary['by_sector'][$sector])) {
                $summary['by_sector'][$sector] = 0;
            }
            $summary['by_sector'][$sector]++;
        }

        return $summary;
    }
}
