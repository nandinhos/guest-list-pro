<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketSale;
use App\Models\User;
use App\Filament\Bilheteria\Pages\CashClosing;
use App\Enums\PaymentMethod;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class CashClosingTest extends TestCase
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

    public function test_can_render_cash_closing_page(): void
    {
        Livewire::test(CashClosing::class)
            ->assertStatus(200);
    }

    public function test_can_filter_sales_by_datetime_range(): void
    {
        $event = Event::first();
        
        // Sale 1: Early morning (Included)
        TicketSale::factory()->create([
            'event_id' => $event->id,
            'created_at' => Carbon::parse('2026-01-22 02:00:00'),
            'buyer_name' => 'Morning Guy',
        ]);

        // Sale 2: Far future (Excluded)
        TicketSale::factory()->create([
            'event_id' => $event->id,
            'created_at' => Carbon::parse('2026-01-23 02:00:00'),
            'buyer_name' => 'Future Guy',
        ]);

        Livewire::test(CashClosing::class)
            ->set('data.start_datetime', '2026-01-22 00:00:00')
            ->set('data.end_datetime', '2026-01-22 04:00:00')
            ->assertSee('Morning Guy')
            ->assertDontSee('Future Guy');
    }

    public function test_can_filter_sales_by_payment_method(): void
    {
        $event = Event::first();
        
        TicketSale::factory()->create([
            'event_id' => $event->id,
            'payment_method' => PaymentMethod::Pix->value,
            'buyer_name' => 'Pix Buyer',
        ]);

        TicketSale::factory()->create([
            'event_id' => $event->id,
            'payment_method' => PaymentMethod::Cash->value,
            'buyer_name' => 'Cash Buyer',
        ]);

        Livewire::test(CashClosing::class)
            ->set('data.payment_method', PaymentMethod::Pix->value)
            ->assertSee('Pix Buyer')
            ->assertDontSee('Cash Buyer');
    }

    public function test_can_export_cash_closing_pdf(): void
    {
        $event = Event::first();
        TicketSale::factory()->create([
            'event_id' => $event->id,
            'buyer_name' => 'Export Guy',
        ]);

        Livewire::test(CashClosing::class)
            ->set('data.start_datetime', now()->startOfDay())
            ->set('data.end_datetime', now())
            ->call('exportPdf')
            ->assertStatus(200);
    }
}
