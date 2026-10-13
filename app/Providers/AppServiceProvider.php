<?php

namespace App\Providers;

use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Configuración global compartida por los 3 paneles de Filament (Admin, Stock y Vendedor).
     */
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel
                ->favicon(asset('img/LogoTiendaNegro.png'))
                ->brandLogoHeight('3.2rem')
                ->brandLogo(fn (): HtmlString => new HtmlString('
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . asset('img/LogoTiendaNegro.png') . '" style="height: 44px; width: 44px; object-fit: contain;" />
                        <img src="' . asset('img/LetrasTiendaNegro.png') . '" style="height: 24px; width: auto; object-fit: contain;" />
                    </div>
                '))
                ->darkModeBrandLogo(fn (): HtmlString => new HtmlString('
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="height: 44px; width: 44px; display: flex; align-items: center; justify-content: center;">
                            <img src="' . asset('img/LogoTiendaBlanco.png') . '" style="height: 92px; width: auto; max-width: none; object-fit: contain; transform: translate(1px, 11.5px);" />
                        </div>
                        <img src="' . asset('img/LetrasTiendaBlanco.png') . '" style="height: 24px; width: auto; object-fit: contain;" />
                    </div>
                '))
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
                ->userMenuItems([
                    MenuItem::make()
                        ->label('Volver a la Tienda')
                        ->url('/')
                        ->icon('heroicon-o-building-storefront'),
                ])
                ->renderHook(
                    PanelsRenderHook::USER_MENU_BEFORE,
                    fn (): string => Blade::render('
                        <button x-data @click="$dispatch(\'theme-changed\', $store.theme === \'dark\' ? \'light\' : \'dark\')" type="button" class="flex items-center justify-center p-2 mr-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors" title="Cambiar tema">
                            <x-filament::icon x-show="$store.theme !== \'dark\'" icon="heroicon-o-moon" class="w-5 h-5 text-gray-700" />
                            <x-filament::icon x-show="$store.theme === \'dark\'" icon="heroicon-o-sun" class="w-5 h-5 text-amber-400" style="display: none;" />
                        </button>
                    ')
                )
                ->renderHook(
                    PanelsRenderHook::HEAD_END,
                    fn (): HtmlString => new HtmlString('<link rel="stylesheet" href="' . asset('css/filament-theme.css') . '" />')
                );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Regla global de seguridad para contraseñas en Registro, Recuperación y Perfil
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->numbers();
        });

        // Personalización del correo de verificación con plantilla HTML corporativa
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirmá tu cuenta en Sound Heritage')
                ->view('emails.verify-email', [
                    'user' => $notifiable,
                    'url' => $url,
                ]);
        });

        // Personalización del correo de restablecimiento de contraseña con plantilla HTML corporativa
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Restablecé tu contraseña - Sound Heritage')
                ->view('emails.reset-password', [
                    'user' => $notifiable,
                    'url' => $url,
                ]);
        });
    }
}