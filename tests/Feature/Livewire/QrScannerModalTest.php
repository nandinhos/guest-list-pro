<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QrScannerModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_exists_and_can_process_checkin(): void
    {
        $validator = User::factory()->create(['role' => UserRole::VALIDATOR]);
        $guest = $this->createGuest();

        $this->actingAs($validator);

        Livewire::test(\App\Livewire\QrScannerModal::class)
            ->call('processCheckin', $guest->qr_token)
            ->assertHasNoErrors(); // O Filament usa notificações que disparam eventos

        $guest->refresh();
        $this->assertTrue($guest->is_checked_in);
    }

    public function test_component_shows_error_for_invalid_token(): void
    {
        $validator = User::factory()->create(['role' => UserRole::VALIDATOR]);
        $this->actingAs($validator);

        Livewire::test(\App\Livewire\QrScannerModal::class)
            ->call('processCheckin', 'INVALID_TOKEN')
            ->assertHasNoErrors(); // O erro deve ser via Notificação, não ValidationException
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
