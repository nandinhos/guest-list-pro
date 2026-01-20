<?php

namespace App\Providers\Filament;

use App\Filament\Bilheteria\Pages\SelectEvent;
use App\Http\Middleware\EnsureEventSelected;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BilheteriaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('bilheteria')
            ->spa()
            ->path('bilheteria')
            ->login()
            ->brandName('Portal da Bilheteria')
            ->colors([
                'primary' => Color::Orange,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->viteTheme('resources/css/filament/bilheteria/theme.css')
            ->discoverResources(in: app_path('Filament/Bilheteria/Resources'), for: 'App\Filament\Bilheteria\Resources')
            ->discoverPages(in: app_path('Filament/Bilheteria/Pages'), for: 'App\Filament\Bilheteria\Pages')
            ->pages([
                SelectEvent::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Bilheteria/Widgets'), for: 'App\Filament\Bilheteria\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                ThrottleRequests::class.':bilheteria',
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
