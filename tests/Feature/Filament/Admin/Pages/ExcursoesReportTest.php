<?php

namespace Tests\Feature\Filament\Admin\Pages;

use App\Enums\DocumentType;
use App\Enums\TipoVeiculo;
use App\Enums\UserRole;
use App\Filament\Admin\Pages\ExcursoesGestao;
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
            ->test(ExcursoesGestao::class)
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
            ->test(ExcursoesGestao::class)
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
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->assertDontSee('Excursão de Outro Evento');
    }

    public function test_tab_count_returns_correct_values(): void
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
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id);

        $instance = $component->instance();
        $this->assertEquals(1, $instance->getTabCount('excursoes'));
        $this->assertEquals(1, $instance->getTabCount('veiculos'));
        $this->assertEquals(1, $instance->getTabCount('monitores'));
    }

    public function test_switch_tab_changes_active_tab(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->call('switchTab', 'veiculos')
            ->assertSet('activeTab', 'veiculos')
            ->call('switchTab', 'monitores')
            ->assertSet('activeTab', 'monitores')
            ->call('switchTab', 'excursoes')
            ->assertSet('activeTab', 'excursoes');
    }

    public function test_create_excursao_saves_with_event_and_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->callTableAction('createExcursao', data: ['nome' => 'Nova Excursão']);

        $this->assertDatabaseHas('excursoes', [
            'nome' => 'Nova Excursão',
            'event_id' => $this->event->id,
            'criado_por' => $this->admin->id,
        ]);
    }

    public function test_edit_excursao_updates_nome(): void
    {
        $excursao = Excursao::factory()->create([
            'event_id' => $this->event->id,
            'nome' => 'Nome Original',
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->callTableAction('editExcursao', $excursao, data: ['nome' => 'Nome Atualizado']);

        $this->assertDatabaseHas('excursoes', ['id' => $excursao->id, 'nome' => 'Nome Atualizado']);
    }

    public function test_delete_excursao_removes_record(): void
    {
        $excursao = Excursao::factory()->create([
            'event_id' => $this->event->id,
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->callTableAction('deleteExcursao', $excursao);

        $this->assertDatabaseMissing('excursoes', ['id' => $excursao->id]);
    }

    public function test_create_veiculo_saves_correctly(): void
    {
        $excursao = Excursao::factory()->create([
            'event_id' => $this->event->id,
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->call('switchTab', 'veiculos')
            ->callTableAction('createVeiculo', data: [
                'tipo' => TipoVeiculo::ONIBUS->value,
                'placa' => 'ABC-1234',
                'excursao_id' => $excursao->id,
            ]);

        $this->assertDatabaseHas('veiculos', [
            'tipo' => TipoVeiculo::ONIBUS->value,
            'placa' => 'ABC-1234',
            'excursao_id' => $excursao->id,
        ]);
    }

    public function test_create_monitor_saves_with_event_and_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->call('switchTab', 'monitores')
            ->callTableAction('createMonitor', data: [
                'nome' => 'Monitor Teste',
                'document_type' => DocumentType::CPF->value,
                'document_number' => '12345678901',
                'veiculo_id' => null,
            ]);

        $this->assertDatabaseHas('monitores', [
            'nome' => 'Monitor Teste',
            'document_number' => '12345678901',
            'event_id' => $this->event->id,
            'criado_por' => $this->admin->id,
        ]);
    }

    public function test_create_monitor_rejects_duplicate_document_same_event(): void
    {
        Monitor::factory()->create([
            'event_id' => $this->event->id,
            'document_type' => DocumentType::CPF,
            'document_number' => '12345678901',
            'criado_por' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExcursoesGestao::class)
            ->set('selectedEventId', $this->event->id)
            ->call('switchTab', 'monitores')
            ->callTableAction('createMonitor', data: [
                'nome' => 'Outro Monitor',
                'document_type' => DocumentType::CPF->value,
                'document_number' => '12345678901',
                'veiculo_id' => null,
            ]);

        $this->assertDatabaseCount('monitores', 1);
    }
}
