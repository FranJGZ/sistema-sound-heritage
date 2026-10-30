<?php

namespace App\Mail;

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

class PurchaseConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $deliveryMethod = null,
        public Collection|array $attendances = []
    ) {}

    public function envelope(): Envelope
    {
        $invoiceNumber = $this->invoice->invoice_type . ' ' .
            str_pad((string)$this->invoice->point_of_sale, 4, '0', STR_PAD_LEFT) . '-' .
            str_pad((string)$this->invoice->receipt_number, 8, '0', STR_PAD_LEFT);

        return new Envelope(
            subject: "Confirmación de Compra #{$invoiceNumber} - Sound Heritage"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-confirmation',
            with: [
                'invoice'        => $this->invoice,
                'deliveryMethod' => $this->deliveryMethod,
                'attendances'    => $this->attendances,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // 1. Factura / Ticket Fiscal en PDF
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

        // 2. Entradas si la compra incluyó eventos
        if (!empty($this->attendances)) {
            foreach ($this->attendances as $index => $attendance) {
                $ticketPdf = Pdf::loadView('reports.event-ticket-pdf', [
                    'record' => $attendance,
                ])->setPaper([0, 0, 226.77, 450], 'portrait');

                $ticketFilename = "Entrada-Evento-" . str_pad((string)$attendance->id, 6, '0', STR_PAD_LEFT) . ".pdf";

                $attachments[] = Attachment::fromData(
                    fn () => $ticketPdf->output(),
                    $ticketFilename
                )->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}

