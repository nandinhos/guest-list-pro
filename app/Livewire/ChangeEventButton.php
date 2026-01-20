<?php

namespace App\Livewire;

use App\Models\Event;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Componente para exibir o evento selecionado e permitir troca.
 */
class ChangeEventButton extends Component
{
    /**
     * Retorna o evento atualmente selecionado.
     */
    public function getSelectedEventProperty(): ?Event
    {
        $eventId = session('selected_event_id');

        if (! $eventId) {
            return null;
        }

        return Event::find($eventId);
    }

    /**
     * Limpa a sessão e redireciona para a página de seleção de evento.
     */
    public function changeEvent(): void
    {
        session()->forget('selected_event_id');

        $panelId = Filament::getCurrentPanel()?->getId() ?? 'promoter';

        $this->redirect(
            route("filament.{$panelId}.pages.select-event"),
            navigate: true
        );
    }

    public function render(): View
    {
        return view('livewire.change-event-button');
    }
}
