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

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GuestService::class);
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
}
