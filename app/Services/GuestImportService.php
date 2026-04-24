<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuestImportService
{
    public array $parsedData = [];

    public array $parsedEvent = [];

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

        $this->parseEventData($content);

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

    protected function parseEventData(string $content): void
    {
        $this->parsedEvent = [];

        if (preg_match('/\*\*Evento:\*\*\s*(.+)/u', $content, $m)) {
            $this->parsedEvent['name'] = trim($m[1]);
        }
        if (preg_match('/\*\*Data:\*\*\s*(\d{2}\/\d{2}\/\d{4})/', $content, $m)) {
            $this->parsedEvent['date'] = \Carbon\Carbon::createFromFormat('d/m/Y', trim($m[1]))->format('Y-m-d');
        }
        if (preg_match('/\*\*Local:\*\*\s*(.+)/u', $content, $m)) {
            $this->parsedEvent['location'] = trim($m[1]);
        }
        if (preg_match('/\*\*Horário:\*\*\s*(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $content, $m)) {
            $this->parsedEvent['start_time'] = trim($m[1]);
            $this->parsedEvent['end_time'] = trim($m[2]);
        }
    }

    public function import(int $adminUserId): array
    {
        $this->importResult = [
            'imported' => 0,
            'duplicates' => 0,
            'errors' => [],
            'warnings' => [],
            'promoters_created' => 0,
        ];

        if (empty($this->parsedEvent['name']) || empty($this->parsedEvent['date'])) {
            $this->importResult['errors'][] = 'Arquivo não contém cabeçalho de evento válido (Evento + Data são obrigatórios)';

            return $this->importResult;
        }

        $event = Event::firstOrCreate(
            [
                'name' => $this->parsedEvent['name'],
                'date' => $this->parsedEvent['date'],
                'location' => $this->parsedEvent['location'] ?? null,
            ],
            [
                'location' => $this->parsedEvent['location'] ?? null,
                'start_time' => $this->parsedEvent['start_time'] ?? null,
                'end_time' => $this->parsedEvent['end_time'] ?? null,
                'status' => \App\Enums\EventStatus::ACTIVE,
            ]
        );

        DB::beginTransaction();
        try {
            $sectorsNeeded = collect($this->parsedData)->pluck('sector_name')->unique();
            $sectors = [];
            foreach ($sectorsNeeded as $sectorName) {
                $sector = Sector::firstOrCreate(
                    ['event_id' => $event->id, 'name' => $sectorName]
                );
                $sectors[$sectorName] = $sector;
            }

            $promoterCache = $this->getPromoterCache($event->id);

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

            $assignments = collect($this->parsedData)
                ->map(fn ($i) => [$i['promoter_name'], $i['sector_name']])
                ->unique(fn ($pair) => implode('|', $pair));

            foreach ($assignments as [$promoterName, $sectorName]) {
                $promoterId = $promoterCache[$promoterName] ?? null;
                $sector = $sectors[$sectorName] ?? null;
                if ($promoterId && $sector) {
                    EventAssignment::firstOrCreate(
                        [
                            'user_id' => $promoterId,
                            'event_id' => $event->id,
                            'sector_id' => $sector->id,
                        ],
                        ['role' => 'promoter']
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->importResult['errors'][] = 'Erro_transaction: '.$e->getMessage();
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
            ->with(['promoter', 'sector'])
            ->first();

        if ($existingGuest) {
            $this->importResult['warnings'][] = [
                'name' => $item['name'],
                'document' => $item['document'],
                'reason' => 'CPF já cadastrado',
                'existing_name' => $existingGuest->name,
                'existing_promoter' => $existingGuest->promoter?->name ?? '—',
                'existing_sector' => $existingGuest->sector?->name ?? '—',
            ];

            return 'duplicate';
        }

        $promoterName = $item['promoter_name'];

        if (! isset($promoterCache[$promoterName])) {
            $promoter = User::where('name', $promoterName)
                ->where('role', 'promoter')
                ->first();

            if (! $promoter) {
                $email = $this->generateEmail($promoterName);
                $existingByEmail = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

                if ($existingByEmail) {
                    $promoter = $existingByEmail;
                } else {
                    $promoter = User::create([
                        'name' => $promoterName,
                        'email' => $email,
                        'password' => bcrypt('password'),
                        'role' => 'promoter',
                        'is_active' => true,
                    ]);
                    $this->importResult['promoters_created']++;
                }
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
            ->all();
    }

    protected function getPromoterCache(int $eventId): array
    {
        return User::where('role', 'promoter')
            ->get()
            ->mapWithKeys(fn ($user) => [$user->name => $user->id])
            ->all();
    }

    protected function generateEmail(string $name): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', trim($ascii)));
        $slug = trim($slug, '.');

        return $slug.'@guestlist.pro';
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
