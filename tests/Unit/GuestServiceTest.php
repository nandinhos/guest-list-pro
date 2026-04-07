<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Guest;
use App\Models\User;
use App\Services\GuestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestServiceTest extends TestCase
{
    use RefreshDatabase;

    private GuestService $service;

    private User $admin;

    private User $validator;

    private User $promoter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GuestService::class);
    }

    public function test_checkin_by_qr_token_success(): void
    {
        $validator = User::factory()->create([
            'role' => UserRole::VALIDATOR,
            'is_active' => true,
        ]);

        $guest = Guest::factory()->create([
            'qr_token' => 'test-token-123',
            'is_checked_in' => false,
        ]);

        $result = $this->service->checkinByQrToken('test-token-123', $validator);

        $this->assertTrue($result['success']);
        $this->assertEquals('Check-in realizado com sucesso!', $result['message']);

        $guest->refresh();
        $this->assertTrue($guest->is_checked_in);
        $this->assertEquals($validator->id, $guest->checked_in_by);
        $this->assertNotNull($guest->checked_in_at);
    }

    public function test_checkin_by_qr_token_admin_can_also_checkin(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $guest = Guest::factory()->create([
            'qr_token' => 'admin-token-456',
            'is_checked_in' => false,
        ]);

        $result = $this->service->checkinByQrToken('admin-token-456', $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals('Check-in realizado com sucesso!', $result['message']);
    }

    public function test_checkin_by_qr_token_returns_error_for_non_validator(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $result = $this->service->checkinByQrToken('any-token', $promoter);

        $this->assertFalse($result['success']);
        $this->assertEquals('Você não tem permissão para realizar check-ins.', $result['message']);
    }

    public function test_checkin_by_qr_token_returns_error_when_token_not_found(): void
    {
        $validator = User::factory()->create([
            'role' => UserRole::VALIDATOR,
            'is_active' => true,
        ]);

        $result = $this->service->checkinByQrToken('non-existent-token', $validator);

        $this->assertFalse($result['success']);
        $this->assertEquals('Convidado não encontrado.', $result['message']);
    }

    public function test_checkin_by_qr_token_returns_error_when_already_checked_in(): void
    {
        $validator = User::factory()->create([
            'role' => UserRole::VALIDATOR,
            'is_active' => true,
        ]);

        $guest = Guest::factory()->create([
            'qr_token' => 'already-checked-token',
            'is_checked_in' => true,
        ]);

        $result = $this->service->checkinByQrToken('already-checked-token', $validator);

        $this->assertFalse($result['success']);
        $this->assertEquals('Este convidado já realizou o check-in.', $result['message']);
    }

    public function test_can_register_guest_returns_true_for_promoter_with_permission(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => null,
            'end_time' => null,
        ]);

        $result = $this->service->canRegisterGuest($promoter, $event->id, $sector->id);

        $this->assertTrue($result['allowed'], 'Expected allowed=true but got: '.($result['message'] ?? 'no message'));
    }

    public function test_can_register_guest_returns_false_for_inactive_promoter(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => false,
        ]);

        $result = $this->service->canRegisterGuest($promoter, 1, 1);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('Usuário sem permissão de promoter ou inativo.', $result['message']);
    }

    public function test_can_register_guest_returns_false_for_non_promoter(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $result = $this->service->canRegisterGuest($admin, 1, 1);

        $this->assertFalse($result['allowed']);
    }

    public function test_can_register_guest_returns_false_without_sector_permission(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector1 = \App\Models\Sector::factory()->create(['event_id' => $event->id]);
        $sector2 = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector1->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => null,
            'end_time' => null,
        ]);

        $result = $this->service->canRegisterGuest($promoter, $event->id, $sector2->id);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('Você não tem permissão para cadastrar convidados neste setor/evento.', $result['message']);
    }

    public function test_can_register_guest_respects_time_window(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => now()->addHour()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
        ]);

        $result = $this->service->canRegisterGuest($promoter, $event->id, $sector->id);

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('O cadastro para este setor só abre às', $result['message']);
    }

    public function test_can_register_guest_respects_guest_limit(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 2,
            'start_time' => null,
            'end_time' => null,
        ]);

        Guest::factory()->count(3)->create([
            'promoter_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
        ]);

        $result = $this->service->canRegisterGuest($promoter, $event->id, $sector->id);

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('Limite de convidados atingido', $result['message']);
    }

    public function test_can_register_guest_returns_remaining_quota(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => null,
            'end_time' => null,
        ]);

        Guest::factory()->count(30)->create([
            'promoter_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector->id,
        ]);

        $result = $this->service->canRegisterGuest($promoter, $event->id, $sector->id);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(70, $result['remaining']);
    }

    public function test_get_authorized_events_returns_events_with_permissions(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event1 = \App\Models\Event::factory()->create();
        $event2 = \App\Models\Event::factory()->create();
        $sector = \App\Models\Sector::factory()->create(['event_id' => $event1->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event1->id,
            'sector_id' => $sector->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => null,
            'end_time' => null,
        ]);

        $events = $this->service->getAuthorizedEvents($promoter);

        $this->assertCount(1, $events);
        $this->assertEquals($event1->id, $events->first()->id);
    }

    public function test_get_authorized_sectors_returns_sectors_with_permissions(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        $event = \App\Models\Event::factory()->create();
        $sector1 = \App\Models\Sector::factory()->create(['event_id' => $event->id]);
        $sector2 = \App\Models\Sector::factory()->create(['event_id' => $event->id]);

        \App\Models\EventAssignment::create([
            'user_id' => $promoter->id,
            'event_id' => $event->id,
            'sector_id' => $sector1->id,
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => 100,
            'start_time' => null,
            'end_time' => null,
        ]);

        $sectors = $this->service->getAuthorizedSectors($promoter, $event->id);

        $this->assertCount(1, $sectors);
        $this->assertEquals($sector1->id, $sectors->first()->id);
    }

    public function test_admin_cannot_checkin_without_validator_role(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $guest = Guest::factory()->create([
            'qr_token' => 'admin-test-token',
            'is_checked_in' => false,
        ]);

        $result = $this->service->checkinByQrToken('admin-test-token', $admin);

        $this->assertTrue($result['success']);
    }

    public function test_inactive_validator_cannot_checkin(): void
    {
        $validator = User::factory()->create([
            'role' => UserRole::VALIDATOR,
            'is_active' => false,
        ]);

        $result = $this->service->checkinByQrToken('any-token', $validator);

        $this->assertFalse($result['success']);
    }
}
