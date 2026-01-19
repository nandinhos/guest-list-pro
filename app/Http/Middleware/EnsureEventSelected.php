<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventSelected
{
    /**
     * Paineis que requerem selecao de evento.
     *
     * @var array<string>
     */
    protected array $panelsRequiringEvent = ['promoter', 'validator', 'bilheteria'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return $next($request);
        }

        $panelId = $panel->getId();

        if (! in_array($panelId, $this->panelsRequiringEvent)) {
            return $next($request);
        }

        // Se ja esta na pagina de selecao de evento, nao redireciona
        if ($request->routeIs("filament.{$panelId}.pages.select-event")) {
            return $next($request);
        }

        // Verifica se ha um evento selecionado na sessao
        if (! session('selected_event_id')) {
            return redirect()->route("filament.{$panelId}.pages.select-event");
        }

        return $next($request);
    }
}
