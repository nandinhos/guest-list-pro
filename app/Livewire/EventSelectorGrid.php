<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EventSelectorGrid extends Component
{
    public string $panelId;

    public function mount(string $panelId): void
    {
        $this->panelId = $panelId;
    }

    /**
     * Retorna os eventos atribuidos ao usuario atual.
     * Para o painel Bilheteria, filtra apenas eventos com bilheteria habilitada.
     *
     * @return Collection<int, \App\Models\Event>
     */
    #[Computed]
    public function events(): Collection
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            return new Collection;
        }

        $events = $user->getAssignedEvents();

        // Filtrar eventos com bilheteria habilitada para o painel Bilheteria
        if ($this->panelId === 'bilheteria') {
            $events = $events->filter(fn ($event) => $event->bilheteria_enabled);
        }

        return $events;
    }

    /**
     * Seleciona um evento e redireciona para o dashboard.
     */
    public function selectEvent(int $eventId): void
    {
        session(['selected_event_id' => $eventId]);

        $this->redirect(
            route("filament.{$this->panelId}.pages.dashboard"),
            navigate: true
        );
    }

    public function render(): View
    {
        return view('livewire.event-selector-grid');
    }
}
