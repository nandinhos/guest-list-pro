<?php

namespace Tests\Feature\Filament\Admin\Pages;

use App\Enums\UserRole;
use App\Filament\Admin\Pages\ExcursoesReport;
use App\Models\Event;
use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExcursoesReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->event = Event::factory()->create();
    }

    public function test_page_is_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/reports/excursoes')
            ->assertOk();
    }

    public function test_shows_empty_state_without_event(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ExcursoesReport::class)
            ->set('selectedEventId', null)
            ->assertSee('Selecione um evento');
    }

    public function test_lists_excursoes_for_selected_event(): void
    {
        Excursao::factory()->create([
            'event_id' => $this->event->id,
            'nome' => 'Excursão Teste ABC',
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesReport::class)
            ->set('selectedEventId', $this->event->id)
            ->assertSee('Excursão Teste ABC');
    }

    public function test_does_not_show_excursoes_from_other_events(): void
    {
        $otherEvent = Event::factory()->create();
        Excursao::factory()->create([
            'event_id' => $otherEvent->id,
            'nome' => 'Excursão de Outro Evento',
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesReport::class)
            ->set('selectedEventId', $this->event->id)
            ->assertDontSee('Excursão de Outro Evento');
    }

    public function test_filter_by_criado_por(): void
    {
        $other = User::factory()->create();

        Excursao::factory()->create([
            'event_id' => $this->event->id,
            'nome' => 'Excursão do Admin',
            'criado_por' => $this->admin->id,
        ]);

        Excursao::factory()->create([
            'event_id' => $this->event->id,
            'nome' => 'Excursão do Outro',
            'criado_por' => $other->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesReport::class)
            ->set('selectedEventId', $this->event->id)
            ->set('criadoPorId', $this->admin->id)
            ->assertSee('Excursão do Admin')
            ->assertDontSee('Excursão do Outro');
    }

    public function test_totais_count_correctly(): void
    {
        $excursao = Excursao::factory()->create([
            'event_id' => $this->event->id,
            'criado_por' => $this->admin->id,
        ]);

        $veiculo = Veiculo::factory()->create(['excursao_id' => $excursao->id]);

        Monitor::factory()->create([
            'event_id' => $this->event->id,
            'veiculo_id' => $veiculo->id,
            'criado_por' => $this->admin->id,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(ExcursoesReport::class)
            ->set('selectedEventId', $this->event->id);

        $this->assertEquals(1, $component->instance()->totais['excursoes']);
        $this->assertEquals(1, $component->instance()->totais['veiculos']);
        $this->assertEquals(1, $component->instance()->totais['monitores']);
    }
}
