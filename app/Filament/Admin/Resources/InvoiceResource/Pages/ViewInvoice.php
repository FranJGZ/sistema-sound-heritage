<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ticket_pdf')
                ->label('Imprimir Ticket Fiscal')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->action(fn () => InvoiceResource::downloadTicketPdf($this->getRecord())),
        ];
    }
}