<?php

namespace App\Providers\Filament;

use App\Filament\Validator\Pages\SelectEvent;
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

class ValidatorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('validator')
            ->path('validator')
            ->loginRouteSlug('login')
            ->spa(false)
            ->brandName('Portal do Validador')
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->viteTheme('resources/css/filament/validator/theme.css')
            ->discoverResources(in: app_path('Filament/Validator/Resources'), for: 'App\Filament\Validator\Resources')
            ->discoverPages(in: app_path('Filament/Validator/Pages'), for: 'App\Filament\Validator\Pages')
            ->pages([
                SelectEvent::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Validator/Widgets'), for: 'App\Filament\Validator\Widgets')
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
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>'
            )
            ->userMenu(false)
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\'change-event-button\')')
            )
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
