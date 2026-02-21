<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestQrTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_generates_qr_token_automatically(): void
    {
        // Cria dependências necessárias
        $event = Event::factory()->create();
        $sector = Sector::factory()->create(['event_id' => $event->id]);
        $promoter = User::factory()->create(['role' => \App\Enums\UserRole::PROMOTER]);

        // Cria o Guest
        $guest = Guest::factory()->create([
            'event_id' => $event->id,
            'sector_id' => $sector->id,
            'promoter_id' => $promoter->id,
            'name' => 'Convidado de Teste',
            'document' => '123456789',
        ]);

        $this->assertNotNull($guest->qr_token);
        // Verifica se é um ULID válido (Laravel 11+ Str::isUlid)
        $this->assertTrue(Str::isUlid($guest->qr_token));
        $this->assertEquals(26, strlen($guest->qr_token));
    }
}
