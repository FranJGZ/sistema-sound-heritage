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
                ->modalDescription(
                    fn () => "¿Confirmás enviar un correo a {$this->record->email} con el enlace para que el usuario restablezca su propia contraseña?"
                )
                ->action(fn () => UserResource::sendPasswordResetLink($this->record)),

            Actions\DeleteAction::make()
                ->label('Suspender')
                ->hidden(fn (): bool => $this->record->id === auth()->id()),
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
