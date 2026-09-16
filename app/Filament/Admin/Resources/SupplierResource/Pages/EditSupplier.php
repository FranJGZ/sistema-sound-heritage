<?php

namespace App\Filament\Admin\Resources\SupplierResource\Pages;

use App\Filament\Admin\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Dar de baja')
                ->modalHeading('Dar de baja Proveedor')
                ->modalDescription('¿Estás seguro de dar de baja este proveedor? Las compras anteriores se mantendrán registradas intactas.')
                ->modalSubmitActionLabel('Sí, dar de baja'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Proveedor actualizado exitosamente';
    }
}
