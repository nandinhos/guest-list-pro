<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\Sector;
use App\Filament\Bilheteria\Resources\TicketSales\Pages\ListTicketSales;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketSalesMobileViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $event = Event::factory()->create();
        session(['selected_event_id' => $event->id]);
        
        Filament::setCurrentPanel(Filament::getPanel('bilheteria'));
    }

    public function test_can_render_ticket_sales_list(): void
    {
        $event = Event::first();
        $sector = Sector::factory()->create(['event_id' => $event->id]);
        TicketSale::factory()->count(3)->create([
            'event_id' => $event->id,
        ]);

        Livewire::test(ListTicketSales::class)
            ->assertStatus(200);
    }

    public function test_mobile_card_is_visible_on_ticket_sales_table(): void
    {
        $event = Event::first();
        TicketSale::factory()->create([
            'event_id' => $event->id,
            'buyer_name' => 'John Doe Mobile Test',
        ]);

        Livewire::test(ListTicketSales::class)
            ->assertSeeHtml('John Doe Mobile Test')
            ->assertSeeHtml('mobile_card');
    }
}
