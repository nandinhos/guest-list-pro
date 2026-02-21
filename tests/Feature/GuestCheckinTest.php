<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use App\Services\GuestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected GuestService $guestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guestService = new GuestService;
    }

    public function test_can_perform_checkin_with_valid_qr_token(): void
    {
        $validator = User::factory()->create(['role' => UserRole::VALIDATOR]);
        $guest = $this->createGuest();

        $result = $this->guestService->checkinByQrToken($guest->qr_token, $validator);

        $this->assertTrue($result['success']);
        $this->assertEquals('Check-in realizado com sucesso!', $result['message']);

        $guest->refresh();
        $this->assertTrue($guest->is_checked_in);
        $this->assertNotNull($guest->checked_in_at);
        $this->assertEquals($validator->id, $guest->checked_in_by);
    }

    public function test_fails_with_invalid_qr_token(): void
    {
        $validator = User::factory()->create(['role' => UserRole::VALIDATOR]);

        $result = $this->guestService->checkinByQrToken('INVALID_TOKEN_1234567890', $validator);

        $this->assertFalse($result['success']);
        $this->assertEquals('Convidado não encontrado.', $result['message']);
    }

    public function test_fails_if_guest_already_checked_in(): void
    {
        $validator = User::factory()->create(['role' => UserRole::VALIDATOR]);
        $guest = $this->createGuest();

        // Simula primeiro check-in
        $this->guestService->checkinByQrToken($guest->qr_token, $validator);

        // Tenta segundo check-in
        $result = $this->guestService->checkinByQrToken($guest->qr_token, $validator);

        $this->assertFalse($result['success']);
        $this->assertEquals('Este convidado já realizou o check-in.', $result['message']);
    }

    public function test_only_authorized_roles_can_perform_checkin(): void
    {
        $promoter = User::factory()->create(['role' => UserRole::PROMOTER]);
        $guest = $this->createGuest();

        $result = $this->guestService->checkinByQrToken($guest->qr_token, $promoter);

        $this->assertFalse($result['success']);
        $this->assertEquals('Você não tem permissão para realizar check-ins.', $result['message']);
    }

    /**
     * Helper para criar um guest com dependências.
     */
    protected function createGuest(): Guest
    {
        $event = Event::factory()->create();
        $sector = Sector::factory()->create(['event_id' => $event->id]);
        $promoter = User::factory()->create(['role' => UserRole::PROMOTER]);

        return Guest::factory()->create([
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'promoter_id' => $promoter->id,
        ]);
    }
}
