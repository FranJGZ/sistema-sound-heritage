<?php

namespace App\Providers\Filament;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Reservation;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
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

class VendedorPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, string $ability, array $arguments = []) {
            if ($user->hasRole('Administrador')) {
                return true;
            }

            if ($user->hasRole('Vendedor')) {
                $target = $arguments[0] ?? null;
                $modelClass = is_object($target) ? get_class($target) : $target;

                // Ventas/Facturas: puede listar, ver detalle y registrar nuevas ventas (no anular ni editar)
                if ($modelClass === Invoice::class) {
                    return in_array($ability, ['viewAny', 'view', 'create'], true);
                }

                // Clientes y Reservas de Sala: puede listar, ver, crear y actualizar (no eliminar)
                if (in_array($modelClass, [Customer::class, Reservation::class], true)) {
                    return in_array($ability, ['viewAny', 'view', 'create', 'update'], true);
                }

                // Catálogo de Productos y Cartelera de Eventos: solo lectura para consulta de stock y precios
                if (in_array($modelClass, [Product::class, Event::class], true)) {
                    return in_array($ability, ['viewAny', 'view'], true);
                }
            }

            return null;
        });
    }

    public function panel(Panel $panel): Panel
    {
        $widgetsFile = app_path('Filament/Admin/Widgets/TiendaDashboardWidgets.php');
        if (file_exists($widgetsFile)) {
            require_once $widgetsFile;
        }

        return $panel
            ->id('vendedor')
            ->path('vendedor')
            ->brandName('Sound Heritage - Ventas')
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
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): \Illuminate\Contracts\View\View => view('filament.theme-toggle')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(view('filament.custom-styles')->render())
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->resources([
                \App\Filament\Admin\Resources\InvoiceResource::class,
                \App\Filament\Admin\Resources\CustomerResource::class,
                \App\Filament\Admin\Resources\ProductResource::class,
                \App\Filament\Admin\Resources\ReservationResource::class,
                \App\Filament\Admin\Resources\EventResource::class,
            ])
            ->widgets([
                \App\Filament\Admin\Widgets\TiendaStatsOverview::class,
                \App\Filament\Admin\Widgets\VentasInteranualesChart::class,
                \App\Filament\Admin\Widgets\TopProductosIngresosChart::class,
                \App\Filament\Admin\Widgets\AlertasTiendaTable::class,
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