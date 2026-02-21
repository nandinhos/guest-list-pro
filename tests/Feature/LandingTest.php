<?php

namespace Tests\Feature;

use App\Livewire\Landing\Benefits;
use App\Livewire\Landing\Features;
use App\Livewire\Landing\Hero;
use App\Livewire\Landing\Index;
use App\Livewire\Landing\RoleCards;
use Livewire\Livewire;
use Tests\TestCase;

class LandingTest extends TestCase
{
    public function test_landing_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeLivewire(Index::class);
    }

    public function test_landing_page_renders_all_sections(): void
    {
        $this->get('/')
            ->assertSeeLivewire(Hero::class)
            ->assertSeeLivewire(Features::class)
            ->assertSeeLivewire(Benefits::class)
            ->assertSeeLivewire(RoleCards::class);
    }

    public function test_hero_section_renders_correctly(): void
    {
        Livewire::test(Hero::class)
            ->assertSee('GuestListPro')
            ->assertSee('Transforme a Gestão do seu Evento')
            ->assertSee('Começar Agora')
            ->assertSee('Explorar Soluções');
    }

    public function test_features_section_renders_correctly(): void
    {
        Livewire::test(Features::class)
            ->assertSee('Gestão de Listas Elite')
            ->assertSee('Check-in Ultra Rápido')
            ->assertSee('Bilheteria Inteligente')
            ->assertSee('Insights Estratégicos');
    }

    public function test_benefits_section_renders_correctly(): void
    {
        Livewire::test(Benefits::class)
            ->assertSee('Padrão GuestListPro Gold')
            ->assertSee('Muito além de uma lista');
    }

    public function test_role_cards_section_renders_links_correctly(): void
    {
        Livewire::test(RoleCards::class)
            ->assertSee('/login?role=admin')
            ->assertSee('/login?role=promoter')
            ->assertSee('/login?role=validator')
            ->assertSee('/login?role=bilheteria');
    }
}
