<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Admin\Resources\EventResource;
use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Admin\Resources\ReservationResource;
use App\Filament\Admin\Widgets\AlertasTiendaTable;
use App\Filament\Admin\Widgets\TiendaStatsOverview;
use App\Filament\Admin\Widgets\TopProductosIngresosChart;
use App\Filament\Admin\Widgets\VentasInteranualesChart;
use App\Models\Attendance;
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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendedorPanelProvider extends PanelProvider
{
    // Define los permisos granulares del rol Vendedor sobre cada modelo del sistema
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

                // Inscripciones a Eventos: puede listar, ver, crear y cancelar inscripciones
                if ($modelClass === Attendance::class) {
                    return in_array($ability, ['viewAny', 'view', 'create', 'delete'], true);
                }

                // Catálogo de Productos y Cartelera de Eventos: solo lectura para consulta de stock y precios
                if (in_array($modelClass, [Product::class, Event::class], true)) {
                    return in_array($ability, ['viewAny', 'view'], true);
                }
            }

            return null;
        });
    }

    // Configura el panel Comercial (/vendedor) con acceso acotado a facturación, clientes y agenda cultural
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
            ->navigationGroups([
                'Tienda',
                'Eventos y Sala',
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->resources([
                InvoiceResource::class,
                CustomerResource::class,
                ProductResource::class,
                ReservationResource::class,
                EventResource::class,
                AttendanceResource::class,
            ])
            ->widgets([
                TiendaStatsOverview::class,
                VentasInteranualesChart::class,
                TopProductosIngresosChart::class,
                AlertasTiendaTable::class,
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