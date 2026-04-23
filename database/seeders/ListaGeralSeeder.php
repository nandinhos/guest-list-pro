<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use App\Models\EventAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ListaGeralSeeder extends Seeder
{
    private array $listaPath = [];

    public function run(): void
    {
        $eventData = [
            'name' => 'XXXPERIENCE 30 ANOS',
            'date' => '2026-04-25',
            'start_time' => '14:00',
            'end_time' => '06:00',
            'location' => 'Fazenda Santa Rita - Itu/SP',
            'status' => EventStatus::ACTIVE,
            'ticket_price' => 0,
            'bilheteria_enabled' => false,
        ];

        $event = Event::create($eventData);

        $pista = Sector::create([
            'event_id' => $event->id,
            'name' => 'PISTA',
            'capacity' => 221,
        ]);

        $backstage = Sector::create([
            'event_id' => $event->id,
            'name' => 'BACKSTAGE',
            'capacity' => 197,
        ]);

        $this->command->info("Evento criado: {$event->name}");
        $this->command->info("Setores: PISTA ({$pista->capacity}), BACKSTAGE ({$backstage->capacity})");

        $this->parseListaFile();
        $this->createPromotersAndGuests($event, $pista, $backstage);
    }

    private function parseListaFile(): void
    {
        $path = base_path('docs/lists/listageral.md');
        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        $currentGroup = null;
        $currentArea = null;

        foreach ($lines as $line) {
            if (preg_match('/^###\s+Convidados\s+(.+?)\s*###$/', $line, $matches)) {
                $currentGroup = trim($matches[1]);
                if (!isset($this->listaPath[$currentGroup])) {
                    $this->listaPath[$currentGroup] = ['PISTA' => [], 'BACKSTAGE' => []];
                }
                $currentArea = null;
                continue;
            }

            if (preg_match('/^#\s*(PISTA|BACKSTAGE)\s*#\s*$/', $line, $matches)) {
                $currentArea = $matches[1];
                continue;
            }

            if ($currentGroup && $currentArea && preg_match('/^(.+?),\s*(.+)$/', $line, $matches)) {
                $this->listaPath[trim($currentGroup)][$currentArea][] = [
                    'name' => trim($matches[1]),
                    'document' => trim($matches[2]),
                ];
            }
        }
    }

    private function normalizeDocument(string $doc): string
    {
        if (preg_match('/^\d{3}\s+\d{3}\s+\d{3}\s+\d{2}$/', $doc)) {
            return preg_replace('/\s+/', '', $doc);
        }
        return trim($doc);
    }

    private function inferDocumentType(string $doc): DocumentType
    {
        return DocumentType::detectFromValue($doc) ?? DocumentType::CPF;
    }

    private function createPromotersAndGuests(Event $event, Sector $pista, Sector $backstage): void
    {
        foreach ($this->listaPath as $promoterName => $areas) {
            $user = User::create([
                'name' => $promoterName,
                'email' => strtolower(str_replace([' ', '(', ')'], ['.', '', ''], $promoterName)) . '@xxxperience.com',
                'password' => Hash::make('password123'),
                'role' => \App\Enums\UserRole::PROMOTER,
                'is_active' => true,
            ]);

            $this->command->info("Promoter criado: {$user->name}");

            $guestCount = 0;

            foreach (['PISTA', 'BACKSTAGE'] as $areaName) {
                $sector = $areaName === 'PISTA' ? $pista : $backstage;
                $guests = $areas[$areaName] ?? [];

                if (empty($guests)) {
                    continue;
                }

                EventAssignment::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'sector_id' => $sector->id,
                    'role' => 'promoter',
                    'guest_limit' => count($guests),
                    'plus_one_enabled' => false,
                ]);

                foreach ($guests as $guestData) {
                    $document = $this->normalizeDocument($guestData['document']);
                    $documentType = $this->inferDocumentType($document);
                    $guestName = mb_strtoupper($guestData['name']);

                    $existing = Guest::where('event_id', $event->id)
                        ->where('document', $document)
                        ->first();

                    if ($existing) {
                        $this->command->warn("  DUPLICADO IGNORADO: {$guestName} ({$document})");
                        continue;
                    }

                    Guest::create([
                        'event_id' => $event->id,
                        'sector_id' => $sector->id,
                        'promoter_id' => $user->id,
                        'name' => $guestName,
                        'document' => $document,
                        'document_type' => $documentType,
                        'email' => null,
                    ]);
                    $guestCount++;
                }

                $this->command->info("  {$areaName}: " . count($guests) . " guests");
            }

            $this->command->info("  TOTAL: {$guestCount} guests");
        }
    }
}
