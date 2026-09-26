<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Registrar Venta y Emitir Ticket')
            ->icon('heroicon-m-printer');
    }

    protected function getRedirectUrlParameters(): array
    {
        return [
            'action' => 'ticket_pdf',
        ];
    }
}