<?php

namespace App\Providers\Filament;

use App\Filament\Promoter\Pages\SelectEvent;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PromoterPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('promoter')
            ->path('promoter')
            ->loginRouteSlug('login')
            ->spa(true)
            ->brandName('Portal do Promoter')
            ->colors([
                'primary' => Color::Purple,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->viteTheme('resources/css/filament/promoter/theme.css')
            ->discoverResources(in: app_path('Filament/Promoter/Resources'), for: 'App\Filament\Promoter\Resources')
            ->discoverPages(in: app_path('Filament/Promoter/Pages'), for: 'App\Filament\Promoter\Pages')
            ->pages([
                SelectEvent::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Promoter/Widgets'), for: 'App\Filament\Promoter\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureEventSelected::class,
            ])
            ->userMenu(false)
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\'change-event-button\')')
            )
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
