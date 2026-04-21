<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\EventStatus;
use App\Enums\PaymentMethod;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Guest;
use App\Models\PaymentSplit;
use App\Models\Sector;
use App\Models\TicketSale;
use App\Models\TicketType;
use App\Models\TicketTypeSector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowcaseTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Showcase Test Seeder ===');
        $this->command->info('Starting...');

        // Clean existing sessions
        DB::table('sessions')->truncate();

        // 1. USERS
        $this->command->info('');
        $this->command->info('1. Creating users...');

        $admin = User::updateOrCreate(
            ['email' => 'admin@guestlist.pro'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );
        $this->command->info('  - admin@guestlist.pro (Admin)');

        $promoter = User::updateOrCreate(
            ['email' => 'promoter@guestlist.pro'],
            [
                'name' => 'Promoter User',
                'password' => bcrypt('password'),
                'role' => UserRole::PROMOTER,
                'is_active' => true,
            ]
        );
        $this->command->info('  - promoter@guestlist.pro (Promoter)');

        $validator = User::updateOrCreate(
            ['email' => 'validador@guestlist.pro'],
            [
                'name' => 'Validator User',
                'password' => bcrypt('password'),
                'role' => UserRole::VALIDATOR,
                'is_active' => true,
            ]
        );
        $this->command->info('  - validador@guestlist.pro (Validator)');

        $bilheteria = User::updateOrCreate(
            ['email' => 'bilheteria@guestlist.pro'],
            [
                'name' => 'Bilheteria User',
                'password' => bcrypt('password'),
                'role' => UserRole::BILHETERIA,
                'is_active' => true,
            ]
        );
        $this->command->info('  - bilheteria@guestlist.pro (Bilheteria)');

        // 2. EVENT
        $this->command->info('');
        $this->command->info('2. Creating event...');

        $event = Event::updateOrCreate(
            ['name' => 'Festival Teste 2026'],
            [
                'date' => Carbon::tomorrow(),
                'start_time' => '18:00:00',
                'end_time' => '23:59:00',
                'location' => 'Arena Showcase',
                'status' => EventStatus::ACTIVE,
                'bilheteria_enabled' => true,
            ]
        );
        $this->command->info("  - Festival Teste 2026 (ID: {$event->id}, bilheteria_enabled: true)");

        // 3. SECTORS
        $this->command->info('');
        $this->command->info('3. Creating sectors...');

        $sectors = [];
        $sectors['Pista'] = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Pista'],
            ['capacity' => 500]
        );
        $this->command->info('  - Pista (capacity: 500)');

        $sectors['VIP'] = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'VIP'],
            ['capacity' => 100]
        );
        $this->command->info('  - VIP (capacity: 100)');

        $sectors['Camarote'] = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Camarote'],
            ['capacity' => 50]
        );
        $this->command->info('  - Camarote (capacity: 50)');

        $sectors['Backstage'] = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Backstage'],
            ['capacity' => 20]
        );
        $this->command->info('  - Backstage (capacity: 20)');

        // 4. TICKET TYPES
        $this->command->info('');
        $this->command->info('4. Creating ticket types...');

        $ticketTypes = [];
        $ticketTypes['Pista Premium'] = TicketType::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Pista Premium'],
            [
                'description' => 'Ingresso para área pista premium',
                'is_active' => true,
                'is_visible' => true,
            ]
        );
        $this->command->info('  - Pista Premium');

        $ticketTypes['VIP Experience'] = TicketType::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'VIP Experience'],
            [
                'description' => 'Experiência VIP completa',
                'is_active' => true,
                'is_visible' => true,
            ]
        );
        $this->command->info('  - VIP Experience');

        $ticketTypes['Camarote Open Bar'] = TicketType::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Camarote Open Bar'],
            [
                'description' => 'Acesso ao camarote com open bar',
                'is_active' => true,
                'is_visible' => true,
            ]
        );
        $this->command->info('  - Camarote Open Bar');

        $ticketTypes['Backstage Pass'] = TicketType::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Backstage Pass'],
            [
                'description' => 'Acesso backstage com encontro com artista',
                'is_active' => true,
                'is_visible' => true,
            ]
        );
        $this->command->info('  - Backstage Pass');

        // 4.1 TICKET TYPE SECTOR PRICES
        $this->command->info('');
        $this->command->info('4.1 Creating sector prices for ticket types...');

        $sectorPrices = [
            'Pista Premium' => [
                'Pista' => 150.00,
                'VIP' => 250.00,
                'Camarote' => 400.00,
                'Backstage' => 300.00,
            ],
            'VIP Experience' => [
                'Pista' => 250.00,
                'VIP' => 350.00,
                'Camarote' => 500.00,
                'Backstage' => 450.00,
            ],
            'Camarote Open Bar' => [
                'Pista' => 400.00,
                'VIP' => 500.00,
                'Camarote' => 600.00,
                'Backstage' => 550.00,
            ],
            'Backstage Pass' => [
                'Pista' => 300.00,
                'VIP' => 450.00,
                'Camarote' => 550.00,
                'Backstage' => 800.00,
            ],
        ];

        foreach ($sectorPrices as $typeName => $prices) {
            foreach ($prices as $sectorName => $price) {
                TicketTypeSector::updateOrCreate(
                    [
                        'ticket_type_id' => $ticketTypes[$typeName]->id,
                        'sector_id' => $sectors[$sectorName]->id,
                    ],
                    ['price' => $price]
                );
            }
            $this->command->info("  - {$typeName}: ".implode(', ', array_map(fn ($s, $p) => "{$s}=R\${$p}", array_keys($prices), $prices)));
        }

        // 5. PROMOTER PERMISSION / EVENT ASSIGNMENT
        $this->command->info('');
        $this->command->info('5. Creating promoter permission...');

        EventAssignment::updateOrCreate(
            [
                'user_id' => $promoter->id,
                'event_id' => $event->id,
            ],
            [
                'role' => UserRole::PROMOTER,
                'guest_limit' => 50,
                'plus_one_enabled' => true,
                'plus_one_limit' => 10,
                'start_time' => Carbon::tomorrow()->setTime(18, 0, 0),
                'end_time' => Carbon::tomorrow()->setTime(23, 59, 0),
            ]
        );
        $this->command->info('  - promoter@guestlist.pro: quota=50, +1 enabled (limit 10)');

        EventAssignment::updateOrCreate(
            [
                'user_id' => $validator->id,
                'event_id' => $event->id,
            ],
            [
                'role' => UserRole::VALIDATOR,
            ]
        );
        $this->command->info('  - validador@guestlist.pro: validator role');

        EventAssignment::updateOrCreate(
            [
                'user_id' => $bilheteria->id,
                'event_id' => $event->id,
            ],
            [
                'role' => UserRole::BILHETERIA,
            ]
        );
        $this->command->info('  - bilheteria@guestlist.pro: bilheteria role');

        // 6. GUESTS (40 total)
        $this->command->info('');
        $this->command->info('6. Creating 40 guests...');

        $guestData = $this->getGuestData();
        $createdGuests = [];

        foreach ($guestData as $index => $data) {
            $guest = Guest::create($data);
            $createdGuests[] = $guest;

            $status = $data['is_checked_in'] ? 'CHECK-IN' : 'PENDENTE';
            $plusOne = isset($data['parent_id']) && $data['parent_id'] ? ' (+1)' : '';
            $this->command->info("  [{$status}] {$data['name']}{$plusOne}");
        }

        // 7. TICKET SALES (20 total)
        $this->command->info('');
        $this->command->info('7. Creating 20 ticket sales...');

        // First, create guests for ticket buyers
        $buyerGuests = $this->createBuyerGuests($event, $sectors, $ticketTypes);

        $salesData = $this->getTicketSalesData($event, $bilheteria, $sectors, $ticketTypes, $buyerGuests);

        // Disable events to prevent observer from sending notifications during seeding
        TicketSale::withoutEvents(function () use ($salesData) {
            foreach ($salesData as $index => $data) {
                $sale = TicketSale::create($data['sale']);

                // Create payment splits if any
                if (! empty($data['splits'])) {
                    foreach ($data['splits'] as $split) {
                        PaymentSplit::create([
                            'ticket_sale_id' => $sale->id,
                            'payment_method' => $split['method'],
                            'value' => $split['value'],
                            'reference' => $split['reference'] ?? null,
                        ]);
                    }
                    $splitMethods = implode(' + ', array_map(fn ($s) => $s['method']->value, $data['splits']));
                    $this->command->info("  - Sale #{$sale->id} ({$data['sale']['buyer_name']}) - Split: {$splitMethods}");
                } else {
                    $this->command->info("  - Sale #{$sale->id} ({$data['sale']['buyer_name']}) - {$data['sale']['payment_method']->value}");
                }
            }
        });

        // 8. APPROVAL REQUESTS (8 total)
        $this->command->info('');
        $this->command->info('8. Creating 8 approval requests...');

        $approvalData = $this->getApprovalRequestsData($event, $validator, $sectors);

        foreach ($approvalData as $data) {
            $approval = ApprovalRequest::create($data);
            $this->command->info("  - Request #{$approval->id} ({$data['guest_name']}) - Status: {$data['status']->value}");
        }

        // 9. CHECKIN ATTEMPTS (25 total)
        $this->command->info('');
        $this->command->info('9. Creating 25 checkin attempts...');

        $checkinData = $this->getCheckinAttemptsData($event, $validator, $createdGuests);

        foreach ($checkinData as $data) {
            CheckinAttempt::create($data);
            $this->command->info("  - Attempt: {$data['result']} for guest_id={$data['guest_id']}");
        }

        $this->command->info('');
        $this->command->info('=== Showcase Test Seeder COMPLETED ===');
        $this->command->info('');
        $this->command->info('USERS:');
        $this->command->info('  admin@guestlist.pro / password');
        $this->command->info('  promoter@guestlist.pro / password');
        $this->command->info('  validador@guestlist.pro / password');
        $this->command->info('  bilheteria@guestlist.pro / password');
        $this->command->info('');
        $this->command->info("EVENT: Festival Teste 2026 (ID: {$event->id})");
    }

    private function getGuestData(): array
    {
        $guests = [];

        // Helper data - deterministic names and documents
        $baseData = [
            // CHECKED IN (15) - Pista
            ['name' => 'Ana Silva', 'doc' => '12345678901', 'sector' => 'Pista', 'checked' => true, 'promoter' => 'promoter'],
            ['name' => 'Bruno Costa', 'doc' => '23456789012', 'sector' => 'Pista', 'checked' => true, 'promoter' => 'promoter'],
            ['name' => 'Carlos Santos', 'doc' => '34567890123', 'sector' => 'Pista', 'checked' => true, 'promoter' => 'admin'],
            ['name' => 'Diana Oliveira', 'doc' => '45678901234', 'sector' => 'Pista', 'checked' => true, 'promoter' => 'admin'],
            ['name' => 'Eduardo Lima', 'doc' => '56789012345', 'sector' => 'Pista', 'checked' => true, 'promoter' => 'promoter'],

            // CHECKED IN (15) - VIP/Camarote/Backstage
            ['name' => 'Fernanda Alves', 'doc' => '67890123456', 'sector' => 'VIP', 'checked' => true, 'promoter' => 'promoter'],
            ['name' => 'Gabriel Rocha', 'doc' => '78901234567', 'sector' => 'VIP', 'checked' => true, 'promoter' => 'admin'],
            ['name' => 'Helena Martins', 'doc' => '89012345678', 'sector' => 'Camarote', 'checked' => true, 'promoter' => 'promoter'],
            ['name' => 'Igor Pereira', 'doc' => '90123456789', 'sector' => 'Camarote', 'checked' => true, 'promoter' => 'admin'],
            ['name' => 'Julia Sousa', 'doc' => '01234567890', 'sector' => 'Backstage', 'checked' => true, 'promoter' => 'promoter'],

            // PENDING (15)
            ['name' => 'Lucas Ferreira', 'doc' => '11223344556', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Maria Gomes', 'doc' => '22334455667', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Nicolas Ribeiro', 'doc' => '33445566778', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Olivia Martins', 'doc' => '44556677889', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Pedro Henrique', 'doc' => '55667788990', 'sector' => 'VIP', 'checked' => false, 'promoter' => 'promoter'],

            // MORE PENDING
            ['name' => 'Quintino Augusto', 'doc' => '66778899001', 'sector' => 'VIP', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Raquel Campos', 'doc' => '77889900112', 'sector' => 'VIP', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Sergio Novaes', 'doc' => '88990011223', 'sector' => 'Camarote', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Tatiana Mendes', 'doc' => '99001122334', 'sector' => 'Camarote', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Umberto Bello', 'doc' => '00112233445', 'sector' => 'Backstage', 'checked' => false, 'promoter' => 'admin'],

            // DUPLICATES (5) - same names, different docs
            ['name' => 'Victor Hugo', 'doc' => '11122233344', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Victor Hugo', 'doc' => '22233344455', 'sector' => 'VIP', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Wellington Dias', 'doc' => '33344455566', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'promoter'],
            ['name' => 'Wellington Dias', 'doc' => '44455566677', 'sector' => 'Camarote', 'checked' => false, 'promoter' => 'admin'],
            ['name' => 'Xavier Souza', 'doc' => '55566677788', 'sector' => 'Pista', 'checked' => false, 'promoter' => 'promoter'],
        ];

        // Get user IDs
        $promoterId = User::where('email', 'promoter@guestlist.pro')->value('id');
        $adminId = User::where('email', 'admin@guestlist.pro')->value('id');
        $eventId = Event::where('name', 'Festival Teste 2026')->value('id');

        foreach ($baseData as $index => $data) {
            $sector = Sector::where('name', $data['sector'])->where('event_id', $eventId)->first();
            $promoterId = $data['promoter'] === 'promoter' ? $promoterId : $adminId;

            $guests[] = [
                'event_id' => $eventId,
                'sector_id' => $sector->id,
                'promoter_id' => $promoterId,
                'name' => $data['name'],
                'document' => $data['doc'],
                'document_type' => DocumentType::CPF,
                'email' => strtolower(str_replace(' ', '.', $data['name'])).'@test.com',
                'is_checked_in' => $data['checked'],
                'checked_in_at' => $data['checked'] ? Carbon::now()->subMinutes(120 - ($index * 5)) : null,
                'checked_in_by' => $data['checked'] ? $adminId : null,
            ];
        }

        // Add +1 companions (5)
        $companionParentId = Guest::where('event_id', $eventId)->where('name', 'Ana Silva')->value('id');
        for ($i = 1; $i <= 5; $i++) {
            $guests[] = [
                'event_id' => $eventId,
                'sector_id' => $sector->id, // Use last sector
                'promoter_id' => $promoterId,
                'parent_id' => $companionParentId,
                'name' => "Companheiro {$i} Silva",
                'document' => '99988877'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'document_type' => DocumentType::CPF,
                'email' => "companheiro{$i}@test.com",
                'is_checked_in' => false,
                'checked_in_at' => null,
                'checked_in_by' => null,
            ];
        }

        return $guests;
    }

    private function createBuyerGuests($event, $sectors, $ticketTypes): array
    {
        $buyerNames = [
            'Roberto Carlos', 'Patricia Andrade', 'Marcos Paulo', 'Juliana Reis', 'Fernando Henrique',
            'Camila Fernanda', 'Rafael Torres', 'Luciana Haiden', 'Daniel Bessa', 'Beatriz Lima',
            'Thiago Mendes', 'Vanessa Castro', 'Leonardo Faro', 'Adriana Claro', 'Marcelo Dantas',
            'Cristina Pike', 'Bruno Ferrari', 'Aline Pinto', 'Gustavo Lima', 'Priscila Agro',
        ];

        $buyerDocs = [
            '91122233344', '92223334455', '93334445566', '94445556677', '95556667788',
            '96667778899', '97778889900', '98889990011', '99990001122', '90000111223',
            '91312312345', '92423423456', '93534534567', '94645645678', '95756756789',
            '96867867890', '97978978901', '98089089012', '99190190123', '90201201234',
        ];

        $bilheteriaId = User::where('email', 'bilheteria@guestlist.pro')->value('id');
        $guests = [];

        for ($i = 0; $i < 20; $i++) {
            $ticketTypeNames = array_keys($ticketTypes);
            $ticketTypeName = $ticketTypeNames[$i % 4];
            $sectorName = $ticketTypeName === 'VIP Experience' ? 'VIP' :
                         ($ticketTypeName === 'Camarote Open Bar' ? 'Camarote' :
                         ($ticketTypeName === 'Backstage Pass' ? 'Backstage' : 'Pista'));

            $guests[] = Guest::create([
                'event_id' => $event->id,
                'sector_id' => $sectors[$sectorName]->id,
                'promoter_id' => $bilheteriaId,
                'name' => $buyerNames[$i],
                'document' => $buyerDocs[$i],
                'document_type' => DocumentType::CPF,
                'email' => strtolower(str_replace(' ', '.', $buyerNames[$i])).'@buyer.com',
                'is_checked_in' => false,
                'checked_in_at' => null,
                'checked_in_by' => null,
            ]);
        }

        return $guests;
    }

    private function getTicketSalesData($event, $bilheteria, $sectors, $ticketTypes, $buyerGuests): array
    {
        $sales = [];

        // Buyer names and documents - deterministic
        $buyers = [
            ['name' => 'Roberto Carlos', 'doc' => '11122233344'],
            ['name' => 'Patricia Andrade', 'doc' => '22233344455'],
            ['name' => 'Marcos Paulo', 'doc' => '33344455566'],
            ['name' => 'Juliana Reis', 'doc' => '44455566677'],
            ['name' => 'Fernando Henrique', 'doc' => '55566677788'],
            ['name' => 'Camila Fernanda', 'doc' => '66677788899'],
            ['name' => 'Rafael Torres', 'doc' => '77788899900'],
            ['name' => 'Luciana Haiden', 'doc' => '88899900011'],
            ['name' => 'Daniel Bessa', 'doc' => '99900011122'],
            ['name' => 'Beatriz Lima', 'doc' => '00011122233'],
            ['name' => 'Thiago Mendes', 'doc' => '12312312345'],
            ['name' => 'Vanessa Castro', 'doc' => '23423423456'],
            ['name' => 'Leonardo Faro', 'doc' => '34534534567'],
            ['name' => 'Adriana Claro', 'doc' => '45645645678'],
            ['name' => 'Marcelo Dantas', 'doc' => '56756756789'],
            ['name' => 'Cristina Pike', 'doc' => '67867867890'],
            ['name' => 'Bruno Ferrari', 'doc' => '78978978901'],
            ['name' => 'Aline Pinto', 'doc' => '89089089012'],
            ['name' => 'Gustavo Lima', 'doc' => '90190190123'],
            ['name' => 'Priscila Agro', 'doc' => '01201201234'],
        ];

        $paymentMethods = [
            PaymentMethod::Cash,
            PaymentMethod::Pix,
            PaymentMethod::CreditCard,
            PaymentMethod::DebitCard,
        ];

        // Create 20 sales
        for ($i = 0; $i < 20; $i++) {
            $buyer = $buyers[$i];
            $method = $paymentMethods[$i % 4];
            $ticketTypeNames = array_keys($ticketTypes);
            $ticketTypeName = $ticketTypeNames[$i % 4];
            $ticketType = $ticketTypes[$ticketTypeName];
            $sectorName = $ticketTypeName === 'VIP Experience' ? 'VIP' :
                         ($ticketTypeName === 'Camarote Open Bar' ? 'Camarote' :
                         ($ticketTypeName === 'Backstage Pass' ? 'Backstage' : 'Pista'));
            $sector = $sectors[$sectorName];

            $sectorPrice = TicketTypeSector::where('ticket_type_id', $ticketType->id)
                ->where('sector_id', $sector->id)
                ->first();

            $price = $sectorPrice?->price ?? 0;

            $saleData = [
                'sale' => [
                    'event_id' => $event->id,
                    'ticket_type_id' => $ticketType->id,
                    'guest_id' => $buyerGuests[$i]->id,
                    'sector_id' => $sector->id,
                    'sold_by' => $bilheteria->id,
                    'value' => $price,
                    'payment_method' => $method,
                    'buyer_name' => $buyer['name'],
                    'buyer_document' => $buyer['doc'],
                    'notes' => null,
                ],
                'splits' => [],
            ];

            // Add splits for specific sales
            if ($i === 5) {
                // Split: 50% cash + 50% pix
                $saleData['splits'] = [
                    ['method' => PaymentMethod::Cash, 'value' => $price * 0.5],
                    ['method' => PaymentMethod::Pix, 'value' => $price * 0.5],
                ];
            } elseif ($i === 10) {
                // Split: 30% credit + 70% debit
                $saleData['splits'] = [
                    ['method' => PaymentMethod::CreditCard, 'value' => $price * 0.3],
                    ['method' => PaymentMethod::DebitCard, 'value' => $price * 0.7],
                ];
            } elseif ($i === 15) {
                // 3-way split
                $saleData['splits'] = [
                    ['method' => PaymentMethod::Cash, 'value' => $price * 0.4],
                    ['method' => PaymentMethod::Pix, 'value' => $price * 0.3],
                    ['method' => PaymentMethod::CreditCard, 'value' => $price * 0.3],
                ];
            }

            $sales[] = $saleData;
        }

        return $sales;
    }

    private function getApprovalRequestsData($event, $validator, $sectors): array
    {
        $requests = [];

        // Pending (3)
        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Pista']->id,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::PENDING,
            'requester_id' => $validator->id,
            'guest_name' => 'Zeca Pagodinho',
            'guest_document' => '12398745600',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'zeca@pagodinho.com',
            'requester_notes' => 'Convidado de última hora autorizado pelo produtor',
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['VIP']->id,
            'type' => RequestType::GUEST_INCLUSION,
            'status' => RequestStatus::PENDING,
            'requester_id' => $validator->id,
            'guest_name' => 'Alcione Rodrigues',
            'guest_document' => '98765432100',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'alcione@brasil.com',
            'requester_notes' => 'VIP especiais esgotados, incluir na lista especial',
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Camarote']->id,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::PENDING,
            'requester_id' => $validator->id,
            'guest_name' => 'Luiz Melodia',
            'guest_document' => '45678912300',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'luiz@melodia.com',
            'requester_notes' => 'Camarote lotado, mas cliente tem direito via purchase',
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        // Approved (3)
        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Pista']->id,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::APPROVED,
            'requester_id' => $validator->id,
            'guest_name' => 'Gilberto Gil',
            'guest_document' => '11122233300',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'gilberto@gil.com',
            'requester_notes' => 'Artista que esqueceu a carteirinha',
            'reviewer_id' => User::where('role', UserRole::ADMIN)->value('id'),
            'reviewed_at' => Carbon::now()->subHours(1),
            'reviewer_notes' => 'Aprovado mediante apresentação de documento com foto',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['VIP']->id,
            'type' => RequestType::GUEST_INCLUSION,
            'status' => RequestStatus::APPROVED,
            'requester_id' => $validator->id,
            'guest_name' => 'Caetano Veloso',
            'guest_document' => '22233344400',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'caetano@veloso.com',
            'requester_notes' => 'Hóspede do hotel oficial',
            'reviewer_id' => User::where('role', UserRole::ADMIN)->value('id'),
            'reviewed_at' => Carbon::now()->subHours(2),
            'reviewer_notes' => 'Aprovado - Guestlist VIP',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Backstage']->id,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::APPROVED,
            'requester_id' => $validator->id,
            'guest_name' => 'Chico Buarque',
            'guest_document' => '33344455500',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'chico@buarque.com',
            'requester_notes' => 'Produtor autorizou entrada backstage',
            'reviewer_id' => User::where('role', UserRole::ADMIN)->value('id'),
            'reviewed_at' => Carbon::now()->subHours(3),
            'reviewer_notes' => 'Aprovado - Produção',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        // Rejected (1)
        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Pista']->id,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::REJECTED,
            'requester_id' => $validator->id,
            'guest_name' => 'Jorge Ben Jor',
            'guest_document' => '44455566600',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'jorge@benjor.com',
            'requester_notes' => 'Fã tentando entrar sem convite',
            'reviewer_id' => User::where('role', UserRole::ADMIN)->value('id'),
            'reviewed_at' => Carbon::now()->subMinutes(30),
            'reviewer_notes' => 'Rejeitado - Sem autorização do produtor',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->addHours(2),
        ];

        // Expired (1)
        $requests[] = [
            'event_id' => $event->id,
            'sector_id' => $sectors['Camarote']->id,
            'type' => RequestType::GUEST_INCLUSION,
            'status' => RequestStatus::PENDING,
            'requester_id' => $validator->id,
            'guest_name' => 'Timóteo Santos',
            'guest_document' => '55566677700',
            'guest_document_type' => DocumentType::CPF,
            'guest_email' => 'timoteo@santos.com',
            'requester_notes' => 'Solicitação pendente',
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ValidatorApp/1.0',
            'expires_at' => Carbon::now()->subHours(1), // Already expired
        ];

        return $requests;
    }

    private function getCheckinAttemptsData($event, $validator, $guests): array
    {
        $attempts = [];

        // Get some checked-in guests
        $checkedInGuests = array_filter($guests, fn ($g) => $g->is_checked_in);

        // Success attempts (15)
        foreach (array_slice($checkedInGuests, 0, 15) as $guest) {
            $attempts[] = [
                'event_id' => $event->id,
                'validator_id' => $validator->id,
                'guest_id' => $guest->id,
                'search_query' => $guest->name,
                'result' => 'success',
                'ip_address' => '192.168.1.'.rand(1, 254),
                'user_agent' => 'ValidatorApp/1.0',
            ];
        }

        // Already checked in (5)
        foreach (array_slice($checkedInGuests, 0, 5) as $guest) {
            $attempts[] = [
                'event_id' => $event->id,
                'validator_id' => $validator->id,
                'guest_id' => $guest->id,
                'search_query' => $guest->document,
                'result' => 'already_checked_in',
                'ip_address' => '192.168.1.'.rand(1, 254),
                'user_agent' => 'ValidatorApp/1.0',
            ];
        }

        // Invalid QR (3)
        $attempts[] = [
            'event_id' => $event->id,
            'validator_id' => $validator->id,
            'guest_id' => $guests[0]->id,
            'search_query' => 'INVALID_QR_TOKEN_123',
            'result' => 'invalid_qr',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'ValidatorApp/1.0',
        ];
        $attempts[] = [
            'event_id' => $event->id,
            'validator_id' => $validator->id,
            'guest_id' => $guests[1]->id,
            'search_query' => 'QR_NOT_FOUND_456',
            'result' => 'invalid_qr',
            'ip_address' => '192.168.1.101',
            'user_agent' => 'ValidatorApp/1.0',
        ];
        $attempts[] = [
            'event_id' => $event->id,
            'validator_id' => $validator->id,
            'guest_id' => $guests[2]->id,
            'search_query' => 'EXPIRED_TOKEN_789',
            'result' => 'invalid_qr',
            'ip_address' => '192.168.1.102',
            'user_agent' => 'ValidatorApp/1.0',
        ];

        // Estorno (2)
        $pendingGuests = array_filter($guests, fn ($g) => ! $g->is_checked_in);
        foreach (array_slice($pendingGuests, 0, 2) as $guest) {
            $attempts[] = [
                'event_id' => $event->id,
                'validator_id' => $validator->id,
                'guest_id' => $guest->id,
                'search_query' => $guest->document,
                'result' => 'estorno',
                'ip_address' => '192.168.1.'.rand(1, 254),
                'user_agent' => 'ValidatorApp/1.0',
            ];
        }

        return $attempts;
    }
}
