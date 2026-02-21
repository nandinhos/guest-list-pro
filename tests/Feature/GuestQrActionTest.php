<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Guests\Pages\ListGuests;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestQrActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_download_qr_action(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $guest = $this->createGuest();

        $this->actingAs($admin);

        Livewire::test(ListGuests::class)
            ->assertTableActionExists('downloadQr', null, $guest);
    }

    public function test_promoter_can_see_download_qr_action(): void
    {
        $promoterUser = User::factory()->create(['role' => UserRole::PROMOTER]);
        $guest = $this->createGuest();
        $guest->update(['promoter_id' => $promoterUser->id]);

        $this->actingAs($promoterUser);

        Livewire::test(\App\Filament\Promoter\Resources\Guests\Pages\ListGuests::class)
            ->assertTableActionExists('downloadQr', null, $guest);
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
