# SPEC-0010 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementar seed da lista XXXPERIENCE (418 guests), sistema de backup/restore, e relatório consolidado de cortesias com exportação PDF/Excel.

**Architecture:** Seed script parser-based para criar evento, setores, users e guests. Sistema de backup via comandos artisan + UI admin. Relatório com agregação SQL e exportação via DomPDF/Excel.

**Tech Stack:** Laravel 12, Filament 4, DomPDF, Maatwebsite Excel, SQLite/MySQL

---

## TASK 1: ListaGeralSeeder - Parser e Estrutura Base

**Files:**
- Create: `database/seeders/ListaGeralSeeder.php`

- [ ] **Step 1: Criar seeder com estrutura básica**

```php
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ListaGeralSeeder extends Seeder
{
    public function run(): void
    {
        // Dados do evento
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

        // Criar evento
        $event = Event::create($eventData);

        // Criar setores
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
    }
}
```

- [ ] **Step 2: Testar seeder parcialmente**

Run: `cd /home/nandodev/projects/guest-list-pro && ./vendor/bin/sail artisan db:seed --class=ListaGeralSeeder`
Expected: INFO "Evento criado: XXXPERIENCE 30 ANOS"

---

## TASK 2: ListaGeralSeeder - Método parseLista

**Files:**
- Modify: `database/seeders/ListaGeralSeeder.php` (adicionar método parseLista)

- [ ] **Step 1: Adicionar método parseLista e propriedades**

Adicionar no seeder:

```php
class ListaGeralSeeder extends Seeder
{
    private array $listaPath = [];

    public function run(): void
    {
        // ... código existente da Task 1 ...

        // Parsear arquivo da lista
        $this->parseListaFile();

        // Criar promoters e guests
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
            // Título de grupo
            if (preg_match('/^###\s+Convidados\s+(.+?)\s*###$/', $line, $matches)) {
                $currentGroup = trim($matches[1]);
                if (!isset($this->listaPath[$currentGroup])) {
                    $this->listaPath[$currentGroup] = ['PISTA' => [], 'BACKSTAGE' => []];
                }
                $currentArea = null;
                continue;
            }

            // Área
            if (preg_match('/^#\s*(PISTA|BACKSTAGE)\s*#\s*$/', $line, $matches)) {
                $currentArea = $matches[1];
                continue;
            }

            // Convidado
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
        // CPF com espaços: "300 386 398 31" → "30038639831"
        if (preg_match('/^\d{3}\s+\d{3}\s+\d{3}\s+\d{2}$/', $doc)) {
            return preg_replace('/\s+/', '', $doc);
        }
        return trim($doc);
    }

    private function inferDocumentType(string $doc): DocumentType
    {
        $doc = trim($doc);
        if (preg_match('/^\d{11}$/', $doc)) {
            return DocumentType::CPF;
        }
        if (str_starts_with(strtoupper($doc), 'RG')) {
            return DocumentType::RG;
        }
        if (str_contains(strtoupper($doc), 'PASSAPORTE')) {
            return DocumentType::PASSAPORTE;
        }
        return DocumentType::CPF; // default
    }

    private function createPromotersAndGuests(Event $event, Sector $pista, Sector $backstage): void
    {
        foreach ($this->listaPath as $promoterName => $areas) {
            // Criar user promoter
            $user = User::create([
                'name' => $promoterName,
                'email' => strtolower(str_replace(' ', '.', $promoterName)) . '@xxxperience.com',
                'password' => Hash::make('password'),
                'role' => \App\Enums\UserRole::PROMOTER,
                'is_active' => true,
            ]);

            $this->command->info("Promoter criado: {$user->name}");

            // Criar guests para cada área
            $guestCount = 0;

            foreach (['PISTA', 'BACKSTAGE'] as $areaName) {
                $sector = $areaName === 'PISTA' ? $pista : $backstage;
                $guests = $areas[$areaName] ?? [];

                if (empty($guests)) {
                    continue;
                }

                // Criar EventAssignment
                EventAssignment::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'sector_id' => $sector->id,
                    'role' => 'promoter',
                    'guest_limit' => count($guests),
                    'plus_one_enabled' => false,
                ]);

                // Criar guests
                foreach ($guests as $guestData) {
                    $document = $this->normalizeDocument($guestData['document']);
                    $documentType = $this->inferDocumentType($document);

                    Guest::create([
                        'event_id' => $event->id,
                        'sector_id' => $sector->id,
                        'promoter_id' => $user->id,
                        'name' => mb_strtoupper($guestData['name']),
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
```

- [ ] **Step 2: Rodar seeder completo**

Run: `cd /home/nandodev/projects/guest-list-pro && ./vendor/bin/sail artisan db:seed --class=ListaGeralSeeder`
Expected: INFO mostrando criação de 13 promoters e 418 guests

- [ ] **Step 3: Verificar dados**

Run: `cd /home/nandodev/projects/guest-list-pro && ./vendor/bin/sail artisan tinker`
```php
Event::count() // deve ser 1
Sector::count() // deve ser 2
User::where('role', 'promoter')->count() // deve ser 13
Guest::count() // deve ser 418
Guest::where('sector_id', 1)->count() // deve ser 221 (PISTA)
Guest::where('sector_id', 2)->count() // deve ser 197 (BACKSTAGE)
```

- [ ] **Step 4: Commit**

```bash
git add database/seeders/ListaGeralSeeder.php
git commit -m "feat: add ListaGeralSeeder for XXXPERIENCE event"
```

---

## TASK 3: Sistema de Backup - Comandos Artisan

**Files:**
- Create: `app/Console/Commands/BackupCreateCommand.php`
- Create: `app/Console/Commands/BackupListCommand.php`
- Create: `app/Console/Commands/BackupRestoreCommand.php`
- Create: `app/Console/Commands/BackupDeleteCommand.php`

- [ ] **Step 1: Criar BackupCreateCommand**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupCreateCommand extends Command
{
    protected $signature = 'backup:create';
    protected $description = 'Create a database backup';

    public function handle(): int
    {
        $this->info('Creating database backup...');

        $backupDir = 'backups';
        if (!Storage::exists($backupDir)) {
            Storage::makeDirectory($backupDir);
        }

        $filename = 'backup_' . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // Para SQLite
        if (config('database.default') === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                copy($dbPath, Storage::path($filepath));
                $this->info("Backup created: {$filename}");
                $this->info("Size: " . number_format(filesize(Storage::path($filepath)) / 1024, 2) . " KB");
                return Command::SUCCESS;
            }
        }

        // Para MySQL - usar mysqldump
        $this->error('MySQL backup not implemented yet. Use SQLite for now.');
        return Command::FAILURE;
    }
}
```

- [ ] **Step 2: Criar BackupListCommand**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list';
    protected $description = 'List all database backups';

    public function handle(): int
    {
        $backupDir = 'backups';

        if (!Storage::exists($backupDir)) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $files = Storage::files($backupDir);

        if (empty($files)) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $this->table(
            ['Filename', 'Size', 'Created'],
            collect($files)->map(function ($file) {
                $info = Storage::getMetadata($file);
                return [
                    basename($file),
                    number_format($info['size'] / 1024, 2) . ' KB',
                    Carbon::createFromTimestamp($info['timestamp'])->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 3: Criar BackupRestoreCommand**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore {filename}';
    protected $description = 'Restore a database backup';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $backupDir = 'backups';
        $filepath = $backupDir . '/' . $filename;

        if (!Storage::exists($filepath)) {
            $this->error("Backup not found: {$filename}");
            return Command::FAILURE;
        }

        if (!$this->confirm('This will overwrite the current database. Are you sure?')) {
            $this->info('Restore cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Restoring database...');

        // Para SQLite
        if (config('database.default') === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            $backupPath = Storage::path($filepath);

            // Fazer backup do banco atual antes de restaurar
            $currentBackup = $backupDir . '/pre_restore_' . date('Y_m_d_His') . '.sql';
            if (file_exists($dbPath)) {
                copy($dbPath, Storage::path($currentBackup));
                $this->info("Current database backed up to: pre_restore_*");
            }

            copy($backupPath, $dbPath);

            // Limpar cache
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            $this->info("Database restored successfully from: {$filename}");
            return Command::SUCCESS;
        }

        $this->error('MySQL restore not implemented yet.');
        return Command::FAILURE;
    }
}
```

- [ ] **Step 4: Criar BackupDeleteCommand**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDeleteCommand extends Command
{
    protected $signature = 'backup:delete {filename}';
    protected $description = 'Delete a database backup';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $backupDir = 'backups';
        $filepath = $backupDir . '/' . $filename;

        if (!Storage::exists($filepath)) {
            $this->error("Backup not found: {$filename}");
            return Command::FAILURE;
        }

        if (!$this->confirm("Delete backup: {$filename}?")) {
            $this->info('Delete cancelled.');
            return Command::SUCCESS;
        }

        Storage::delete($filepath);
        $this->info("Backup deleted: {$filename}");

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 5: Testar comandos**

Run:
```bash
./vendor/bin/sail artisan backup:create
./vendor/bin/sail artisan backup:list
./vendor/bin/sail artisan backup:delete backup_2026_04_23_120000.sql  # usar nome real
```

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/Backup*.php
git commit -m "feat: add backup commands (create, list, restore, delete)"
```

---

## TASK 4: Página de Relatório Consolidado

**Files:**
- Create: `app/Filament/Admin/Pages/GuestsReport.php`

- [ ] **Step 1: Criar página GuestsReport**

```php
<?php

namespace App\Filament\Admin\Pages;

use App\Models\Guest;
use App\Models\Event;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class GuestsReport extends Page
{
    protected static string $view = 'filament.admin.pages.guests-report';

    protected static ?string $title = 'Relatório de Cortesias';
    protected static ?string $slug = 'reports/guests-summary';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?int $navigationSort = 3;

    public ?int $selectedEventId = null;

    public function mount(): void
    {
        $this->selectedEventId = session('selected_event_id') ?? Event::first()?->id;
    }

    #[Computed]
    public function reportData(): array
    {
        if (!$this->selectedEventId) {
            return [];
        }

        return Guest::query()
            ->where('event_id', $this->selectedEventId)
            ->select([
                'promoter_id',
                'sector_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_checked_in = 1 THEN 1 ELSE 0 END) as validated'),
            ])
            ->with(['promoter:id,name', 'sector:id,name'])
            ->groupBy('promoter_id', 'sector_id')
            ->get()
            ->groupBy('promoter_id')
            ->map(function ($items, $promoterId) {
                $promoterName = $items->first()->promoter->name;
                $pista = $items->where('sector_id', $items->first()->sector_id === 1 ? 1 : 2)->first();
                $backstage = $items->where('sector_id', $items->first()->sector_id === 2 ? 2 : 1)->first();

                $pistaTotal = $items->where(fn ($item) => $item->sector_id === 1)->sum('total');
                $pistaValidated = $items->where(fn ($item) => $item->sector_id === 1)->sum('validated');
                $backstageTotal = $items->where(fn ($item) => $item->sector_id === 2)->sum('total');
                $backstageValidated = $items->where(fn ($item) => $item->sector_id === 2)->sum('validated');

                return [
                    'promoter_name' => $promoterName,
                    'pista_total' => $pistaTotal,
                    'pista_validated' => $pistaValidated,
                    'backstage_total' => $backstageTotal,
                    'backstage_validated' => $backstageValidated,
                    'total' => $pistaTotal + $backstageTotal,
                    'total_validated' => $pistaValidated + $backstageValidated,
                ];
            })
            ->values()
            ->toArray();
    }

    #[Computed]
    public function totals(): array
    {
        $data = $this->reportData;
        return [
            'pista_total' => collect($data)->sum('pista_total'),
            'pista_validated' => collect($data)->sum('pista_validated'),
            'backstage_total' => collect($data)->sum('backstage_total'),
            'backstage_validated' => collect($data)->sum('backstage_validated'),
            'grand_total' => collect($data)->sum('total'),
            'grand_validated' => collect($data)->sum('total_validated'),
        ];
    }

    public function getEvents(): array
    {
        return Event::where('status', 'active')
            ->pluck('name', 'id')
            ->toArray();
    }
}
```

- [ ] **Step 2: Criar view blade**

```php
<?php
// resources/views/filament/admin/pages/guests-report.blade.php
?>

<div class="fi-page">
    <div class="fi-header">
        <h1 class="fi-title">{{ $this->title }}</h1>
    </div>

    <div class="mb-4">
        <select wire:model.live="selectedEventId" class="fi-select">
            @foreach($this->getEvents() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    @if($this->reportData)
        <div class="overflow-hidden rounded-xl bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Responsável</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">🎟 PISTA</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">🎭 BACKSTAGE</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Entregues</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Validados</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($this->reportData as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['promoter_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $row['pista_total'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $row['backstage_total'] }}</td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">{{ $row['total'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-success-600">{{ $row['total'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-warning-600">{{ $row['total_validated'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">TOTAL</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">{{ $this->totals['pista_total'] }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">{{ $this->totals['backstage_total'] }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">{{ $this->totals['grand_total'] }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-success-600">{{ $this->totals['grand_total'] }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-warning-600">{{ $this->totals['grand_validated'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 flex gap-2">
            <button wire:click="exportPdf" class="fi-btn bg-primary-600 hover:bg-primary-500 text-white px-4 py-2 rounded">
                Exportar PDF
            </button>
            <button wire:click="exportExcel" class="fi-btn bg-success-600 hover:bg-success-500 text-white px-4 py-2 rounded">
                Exportar Excel
            </button>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            Selecione um evento para ver o relatório.
        </div>
    @endif
</div>
```

- [ ] **Step 3: Registrar página no AdminPanelProvider**

Modify: `app/Providers/Filament/AdminPanelProvider.php`

Adicionar no método `navigation()`:
```php
->pages([
    // ... existing pages
    \App\Filament\Admin\Pages\GuestsReport::class,
])
```

- [ ] **Step 4: Testar página**

Run: `./vendor/bin/sail artisan filament:cache-components`
Access: `/admin/reports/guests-summary`

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Admin/Pages/GuestsReport.php resources/views/filament/admin/pages/guests-report.blade.php
git add app/Providers/Filament/AdminPanelProvider.php
git commit -m "feat: add guests summary report page"
```

---

## TASK 5: Exportação PDF do Relatório

**Files:**
- Create: `resources/views/pdf/guests-report.blade.php`

- [ ] **Step 1: Criar template PDF**

```blade
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Cortesias - {{ $eventName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #f97316; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #f97316; margin-bottom: 5px; }
        .header h2 { font-size: 14px; color: #666; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f8f8; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .totals-row { background: #fff3e0 !important; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Cortesias</h1>
            <h2>{{ $eventName }} - {{ $eventDate }}</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30%">Responsável</th>
                    <th class="text-center">🎟 PISTA</th>
                    <th class="text-center">🎭 BACKSTAGE</th>
                    <th class="text-center">TOTAL</th>
                    <th class="text-center">Entregues</th>
                    <th class="text-center">Validados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row['promoter_name'] }}</td>
                    <td class="text-center">{{ $row['pista_total'] }}</td>
                    <td class="text-center">{{ $row['backstage_total'] }}</td>
                    <td class="text-center font-bold">{{ $row['total'] }}</td>
                    <td class="text-center text-success">{{ $row['total'] }}</td>
                    <td class="text-center text-warning">{{ $row['total_validated'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td>TOTAL GERAL</td>
                    <td class="text-center">{{ $totals['pista_total'] }}</td>
                    <td class="text-center">{{ $totals['backstage_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_total'] }}</td>
                    <td class="text-center">{{ $totals['grand_validated'] }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Gerado em: {{ $generatedAt }} | Usuário: {{ $generatedBy }}
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 2: Adicionar método exportPdf na página**

Modify: `app/Filament/Admin/Pages/GuestsReport.php`

Adicionar:
```php
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

public function exportPdf()
{
    $event = Event::find($this->selectedEventId);

    $data = [
        'eventName' => $event->name,
        'eventDate' => $event->date->format('d/m/Y'),
        'data' => $this->reportData,
        'totals' => $this->totals,
        'generatedBy' => Auth::user()->name,
        'generatedAt' => now()->format('d/m/Y H:i:s'),
    ];

    $pdf = Pdf::loadView('pdf.guests-report', $data);

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'relatorio-cortesias-' . $event->id . '.pdf',
        ['Content-Type' => 'application/pdf']
    );
}
```

- [ ] **Step 3: Testar exportação PDF**

Run: `./vendor/bin/sail artisan optimize:clear`
Access: `/admin/reports/guests-summary` e clicar em "Exportar PDF"

- [ ] **Step 4: Commit**

```bash
git add resources/views/pdf/guests-report.blade.php
git add app/Filament/Admin/Pages/GuestsReport.php
git commit -m "feat: add PDF export for guests report"
```

---

## TASK 6: Exportação Excel do Relatório

**Files:**
- Create: `app/Exports/GuestsReportExport.php`

- [ ] **Step 1: Criar Export class**

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GuestsReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $data,
        private array $totals,
        private string $eventName,
    ) {}

    public function collection(): Collection
    {
        $rows = collect($this->data)->map(function ($row) {
            return [
                $row['promoter_name'],
                $row['pista_total'],
                $row['backstage_total'],
                $row['total'],
                $row['total'],
                $row['total_validated'],
            ];
        });

        // Adicionar totais
        $rows->push([
            'TOTAL GERAL',
            $this->totals['pista_total'],
            $this->totals['backstage_total'],
            $this->totals['grand_total'],
            $this->totals['grand_total'],
            $this->totals['grand_validated'],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Responsável',
            '🎟 PISTA',
            '🎭 BACKSTAGE',
            'TOTAL',
            'Entregues',
            'Validados',
        ];
    }
}
```

- [ ] **Step 2: Adicionar método exportExcel na página**

Modify: `app/Filament/Admin/Pages/GuestsReport.php`

Adicionar:
```php
use App\Exports\GuestsReportExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportExcel()
{
    $event = Event::find($this->selectedEventId);

    $export = new GuestsReportExport(
        data: $this->reportData,
        totals: $this->totals,
        eventName: $event->name,
    );

    return Excel::download($export, 'relatorio-cortesias-' . $event->id . '.xlsx');
}
```

- [ ] **Step 3: Testar exportação Excel**

Access: `/admin/reports/guests-summary` e clicar em "Exportar Excel"

- [ ] **Step 4: Commit**

```bash
git add app/Exports/GuestsReportExport.php
git add app/Filament/Admin/Pages/GuestsReport.php
git commit -m "feat: add Excel export for guests report"
```

---

## TASK 7: Página de Gestão de Backups (UI Admin)

**Files:**
- Create: `app/Filament/Admin/Pages/BackupManagement.php`
- Create: `resources/views/filament/admin/pages/backup-management.blade.php`

- [ ] **Step 1: Criar página BackupManagement**

```php
<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class BackupManagement extends Page
{
    protected static string $view = 'filament.admin.pages.backup-management';
    protected static ?string $title = 'Gestão de Backups';
    protected static ?string $slug = 'backups';
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?int $navigationSort = 99;

    #[Computed]
    public function backups(): array
    {
        $backupDir = 'backups';

        if (!Storage::exists($backupDir)) {
            return [];
        }

        return collect(Storage::files($backupDir))
            ->filter(fn ($file) => str_ends_with($file, '.sql') || str_ends_with($file, '.sqlite'))
            ->map(function ($file) {
                $info = Storage::getMetadata($file);
                return [
                    'filename' => basename($file),
                    'size' => number_format($info['size'] / 1024, 2) . ' KB',
                    'created' => Carbon::createFromTimestamp($info['timestamp'])->format('d/m/Y H:i:s'),
                ];
            })
            ->sortByDesc('created')
            ->values()
            ->toArray();
    }

    public function createBackup(): void
    {
        \Illuminate\Support\Facades\Artisan::call('backup:create');
        $this->dispatch('refresh');
    }

    public function deleteBackup(string $filename): void
    {
        \Illuminate\Support\Facades\Artisan::call('backup:delete', ['filename' => $filename]);
        $this->dispatch('refresh');
    }
}
```

- [ ] **Step 2: Criar view blade para backup management**

```blade
<?php
// resources/views/filament/admin/pages/backup-management.blade.php
?>

<div class="fi-page">
    <div class="fi-header">
        <h1 class="fi-title">{{ $this->title }}</h1>
    </div>

    <div class="mb-4">
        <button wire:click="createBackup" class="fi-btn bg-primary-600 hover:bg-primary-500 text-white px-4 py-2 rounded">
            + Criar Backup
        </button>
    </div>

    @if(count($this->backups) > 0)
        <div class="overflow-hidden rounded-xl bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Arquivo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tamanho</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Criado em</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($this->backups as $backup)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $backup['filename'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $backup['size'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $backup['created'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            Nenhum backup encontrado.
        </div>
    @endif
</div>
```

- [ ] **Step 3: Registrar página no AdminPanelProvider**

Modify: `app/Providers/Filament/AdminPanelProvider.php`

- [ ] **Step 4: Testar página**

Access: `/admin/backups`

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Admin/Pages/BackupManagement.php resources/views/filament/admin/pages/backup-management.blade.php
git commit -m "feat: add backup management UI page"
```

---

## TASK 8: Testes Finais e Validação

- [ ] **Step 1: Rodar todos os testes**

Run: `./vendor/bin/sail artisan test`

- [ ] **Step 2: Verificar se não há erros de syntax**

Run: `./vendor/bin/sail php -l app/`

- [ ] **Step 3: Limpar caches**

Run:
```bash
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan filament:cache-components
```

- [ ] **Step 4: Commit final se tudo passar**

```bash
git add -A
git commit -m "feat: implement SPEC-0010 - seed, backup, and guest report"
```

---

## SPEC Coverage Checklist

- [x] Seed script com 418 guests (221 PISTA + 197 BACKSTAGE)
- [x] 13 promoters criados
- [x] Evento "XXXPERIENCE 30 ANOS" criado
- [x] Backup commands (create, list, restore, delete)
- [x] Backup UI page
- [x] Guests report page
- [x] PDF export
- [x] Excel export
- [x] Detalhamento por responsável

---

## Rollback

Se algo falhar:
```bash
# Para limpar dados e recomeçar o seed:
./vendor/bin/sail artisan migrate:fresh --seed=DatabaseSeeder
```
