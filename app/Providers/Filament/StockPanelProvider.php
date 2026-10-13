<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Admin\Resources\PurchaseResource;
use App\Filament\Admin\Resources\SupplierResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StockPanelProvider extends PanelProvider
{
    // Configura el panel de Depósito (/stock) exponiendo únicamente los recursos de inventario y compras
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('stock')
            ->path('stock')
            ->brandName('Sound Heritage - Stock')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->resources([
                ProductResource::class,
                PurchaseResource::class,
                SupplierResource::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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