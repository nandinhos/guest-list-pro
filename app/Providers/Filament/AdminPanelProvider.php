<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\BackupManagement;
use App\Filament\Admin\Pages\GuestsReport;
use App\Filament\Admin\Pages\ImportGuestsPage;
use App\Filament\Admin\Pages\ProfilePage;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\ApprovalMetricsChart;
use App\Filament\Widgets\GuestsVsTicketsChart;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\SalesTimelineChart;
use App\Filament\Widgets\SectorMetricsTable;
use App\Filament\Widgets\TicketTypeReportTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->loginRouteSlug('login')
            ->spa(false)
            ->brandName('Guest List Pro')
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                GuestsReport::class,
                BackupManagement::class,
                ImportGuestsPage::class,
                ProfilePage::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                SalesTimelineChart::class,
                AdminOverview::class,
                PendingApprovalsWidget::class,
                ApprovalMetricsChart::class,
                GuestsVsTicketsChart::class,
                SectorMetricsTable::class,
                TicketTypeReportTable::class,
                // CheckinFlowChart::class,          // ERRO - investigar
                // PromoterPerformanceChart::class,  // ERRO - investigar
                // SectorOccupancyChart::class,      // ERRO - investigar
                // SuspiciousCheckins::class,        // ERRO - investigar
                // RequestsTimelineChart::class,    // ERRO - investigar
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
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Meu Perfil')
                    ->url(fn () => route('filament.admin.pages.profile'))
                    ->icon('heroicon-o-user-circle'),
            ]);
    }
}
