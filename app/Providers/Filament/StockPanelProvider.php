<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class StockPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
                return $panel
            ->id('stock')
            ->path('stock')
            ->brandName('Sound Heritage - Stock')
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
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): \Illuminate\Contracts\View\View => view('filament.theme-toggle')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(view('filament.custom-styles')->render())
            )
            ->discoverResources(in: app_path('Filament/Stock/Resources'), for: 'App\\Filament\\Stock\\Resources')
            ->discoverPages(in: app_path('Filament/Stock/Pages'), for: 'App\\Filament\\Stock\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Stock/Widgets'), for: 'App\\Filament\\Stock\\Widgets')
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
            ->resources([
                \App\Filament\Admin\Resources\ProductResource::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
