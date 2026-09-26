<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Administrador') ? true : null;
        });
    }

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
            ->brandLogo(asset('img/Logo_SH.png'))
            ->brandLogoHeight('3.2rem')
            ->font('Figtree')
            ->colors([
                'primary'   => Color::hex('#b48d56'), // Dorado corporativo
                'secondary' => Color::hex('#0b1031'), // Azul marino corporativo
                'gray'      => Color::Slate,
                'info'      => Color::Blue,
                'success'   => Color::Emerald,
                'warning'   => Color::Amber,
                'danger'    => Color::Rose,
            ])
            ->navigationGroups([
                'Tienda',
                'Eventos y Sala',
                'Administración y RRHH',
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): \Illuminate\Contracts\View\View => view('filament.theme-toggle')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(view('filament.custom-styles')->render())
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Admin\Widgets\TiendaStatsOverview::class,
                \App\Filament\Admin\Widgets\VentasInteranualesChart::class,
                \App\Filament\Admin\Widgets\CrecimientoClientesChart::class,
                \App\Filament\Admin\Widgets\AlertasTiendaTable::class,
                \App\Filament\Admin\Widgets\TopProductosIngresosChart::class,
                \App\Filament\Admin\Widgets\SegmentacionClientesChart::class,
                \App\Filament\Admin\Widgets\DistribucionMontoFacturasChart::class,
                \App\Filament\Admin\Widgets\AnalisisMargenProductosChart::class,
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