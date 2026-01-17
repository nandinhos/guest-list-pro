<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Página temporária para demonstrar e testar os componentes do Design System.
 * Remover após testes concluídos.
 */
class ComponentShowcase extends Component
{
    public function render()
    {
        return view('livewire.component-showcase')
            ->layout('components.layouts.landing');
    }
}
