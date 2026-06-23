<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseQuotation;

class QuotationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public PurchaseQuotation $quotation;

    public function __construct(PurchaseQuotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[3DHub] Purchase Quotation Request Received – ' . $this->quotation->purchase_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
