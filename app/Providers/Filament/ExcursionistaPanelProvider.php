<?php

namespace App\Providers\Filament;

use App\Filament\Excursionista\Pages\SelectEvent;
use App\Filament\Excursionista\Widgets\ExcursionistaStatsWidget;
use App\Http\Middleware\EnsureEventSelected;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ExcursionistaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('excursionista')
            ->path('excursionista')
            ->loginRouteSlug('login')
            ->spa(false)
            ->brandName('Portal do Excursionista')
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->viteTheme('resources/css/filament/excursionista/theme.css')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->discoverResources(in: app_path('Filament/Excursionista/Resources'), for: 'App\Filament\Excursionista\Resources')
            ->discoverPages(in: app_path('Filament/Excursionista/Pages'), for: 'App\Filament\Excursionista\Pages')
            ->pages([
                SelectEvent::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Excursionista/Widgets'), for: 'App\Filament\Excursionista\Widgets')
            ->widgets([
                ExcursionistaStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureEventSelected::class,
            ])
            ->userMenu(false)
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\'change-event-button\')')
            );
    }
}
