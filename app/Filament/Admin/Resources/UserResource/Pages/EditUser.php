<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('enviar_reset')
                ->label('Enviar Recuperación por Email')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Enviar Enlace de Recuperación de Contraseña')
                ->modalDescription(fn () => "¿Confirmás enviar un correo a {$this->record->email} con el enlace para que el usuario restablezca su propia contraseña?")
                ->action(function () {
                    $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $this->record->email]);

                    if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
                        \Filament\Notifications\Notification::make()
                            ->title('Correo de recuperación enviado')
                            ->body("Se envió el enlace de restablecimiento a {$this->record->email}.")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('No se pudo enviar el correo')
                            ->body(__($status))
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->record->syncProfileFromRoles();
    }
}
