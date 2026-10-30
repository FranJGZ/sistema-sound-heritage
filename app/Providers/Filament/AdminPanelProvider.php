<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\AlertasTiendaTable;
use App\Filament\Admin\Widgets\CrecimientoClientesChart;
use App\Filament\Admin\Widgets\DistribucionMontoFacturasChart;
use App\Filament\Admin\Widgets\SegmentacionClientesChart;
use App\Filament\Admin\Widgets\TiendaStatsOverview;
use App\Filament\Admin\Widgets\TopProductosIngresosChart;
use App\Filament\Admin\Widgets\VentasInteranualesChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    // Otorga acceso total a cualquier política o recurso cuando el usuario tiene el rol Administrador
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Administrador') ? true : null;
        });
    }

    // Configura el panel principal de Administración (/admin) con todos los módulos y métricas del ERP
    public function panel(Panel $panel): Panel
    {
        $widgetsFile = app_path('Filament/Admin/Widgets/TiendaDashboardWidgets.php');
        if (file_exists($widgetsFile)) {
            require_once $widgetsFile;
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Sound Heritage')
            ->navigationGroups([
                'Tienda',
                'Eventos y Sala',
                'Administración y RRHH',
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                TiendaStatsOverview::class,
                VentasInteranualesChart::class,
                CrecimientoClientesChart::class,
                AlertasTiendaTable::class,
                TopProductosIngresosChart::class,
                SegmentacionClientesChart::class,
                DistribucionMontoFacturasChart::class,
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
            ]);
    }
}