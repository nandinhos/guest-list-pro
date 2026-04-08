<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestQrActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_model_has_qr_token(): void
    {
        $guest = $this->createGuest();

        $this->assertNotNull($guest->qr_token);
        $this->assertNotEmpty($guest->qr_token);
    }

    public function test_guest_can_be_converted_to_array_with_qr(): void
    {
        $guest = $this->createGuest();
        $guestArray = $guest->toArray();

        $this->assertArrayHasKey('qr_token', $guestArray);
    }

    public function test_guest_generates_unique_qr_token(): void
    {
        $guest1 = $this->createGuest();
        $guest2 = $this->createGuest();

        $this->assertNotEquals($guest1->qr_token, $guest2->qr_token);
    }

    public function test_guest_qr_token_is_ulid(): void
    {
        $guest = $this->createGuest();

        $this->assertMatchesRegularExpression('/^[0-9A-Z]{26}$/', $guest->qr_token);
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
