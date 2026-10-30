<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class EventTicketsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public Collection $attendances;
    public ?Invoice $invoice = null;

    public function __construct(
        mixed $customer = null,
        mixed $attendances = null,
        mixed $invoice = null
    ) {
        if ($customer instanceof Customer) {
            $this->customer = $customer;
            $this->attendances = $attendances instanceof Collection ? $attendances : collect($attendances ?: []);
            $this->invoice = $invoice instanceof Invoice ? $invoice : null;
        } else {
            // Soporta llamadas posicionales donde el primer argumento es la colección de asistencias
            $this->attendances = $customer instanceof Collection ? $customer : collect($customer ?: []);
            $firstAttendance = $this->attendances->first();
            $this->customer = ($firstAttendance && $firstAttendance->customer)
                ? $firstAttendance->customer
                : ($attendances instanceof Customer ? $attendances : new Customer(['first_name' => 'Cliente', 'last_name' => '']));
            $this->invoice = $invoice instanceof Invoice ? $invoice : null;
        }
    }

    public function envelope(): Envelope
    {
        $firstEvent = collect($this->attendances)->first()?->event?->name ?? 'Evento Cultural';

        return new Envelope(
            subject: "Tus Entradas para {$firstEvent} - Sound Heritage"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-tickets',
            with: [
                'customer'    => $this->customer,
                'attendances' => $this->attendances,
                'invoice'     => $this->invoice,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // 1. Entradas individuales en PDF
        foreach ($this->attendances as $attendance) {
            $ticketPdf = Pdf::loadView('reports.event-ticket-pdf', [
                'record' => $attendance,
            ])->setPaper([0, 0, 226.77, 450], 'portrait');

            $ticketFilename = "Entrada-" . str_pad((string)$attendance->id, 6, '0', STR_PAD_LEFT) . ".pdf";

            $attachments[] = Attachment::fromData(
                fn () => $ticketPdf->output(),
                $ticketFilename
            )->withMime('application/pdf');
        }

        // 2. Factura / Comprobante si existe
        if ($this->invoice) {
            $itemCount = max(1, $this->invoice->invoiceDetails->count());
            $height = 430 + ($itemCount * 38);

            $invoicePdf = Pdf::loadView('reports.invoice-ticket-pdf', [
                'invoice' => $this->invoice,
            ])->setPaper([0, 0, 226.77, $height], 'portrait');

            $invoiceFilename = "ticket-FC-{$this->invoice->invoice_type}-{$this->invoice->point_of_sale}-{$this->invoice->receipt_number}.pdf";

            $attachments[] = Attachment::fromData(
                fn () => $invoicePdf->output(),
                $invoiceFilename
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}

