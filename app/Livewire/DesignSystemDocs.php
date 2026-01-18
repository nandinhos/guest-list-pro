<?php

namespace App\Livewire;

use App\Support\Docs\ComponentRegistry;
use Livewire\Component;

/**
 * Página de documentação do Design System.
 * Exibe todos os componentes com demonstração visual, descrição e código.
 */
class DesignSystemDocs extends Component
{
    public string $activeSection = 'button';

    public function mount(): void
    {
        $this->activeSection = request()->get('section', 'button');
    }

    public function setActiveSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function getComponentsProperty(): array
    {
        return ComponentRegistry::all();
    }

    public function render()
    {
        return view('livewire.design-system-docs', [
            'comps' => $this->components,
        ])->layout('components.layouts.docs');
    }
}
