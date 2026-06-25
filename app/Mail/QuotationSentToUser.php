<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseQuotation;

use Illuminate\Mail\Mailables\Attachment;

class QuotationSentToUser extends Mailable
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
            subject: '[3DHub] Your Purchase Quotation - ' . $this->quotation->purchase_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation-sent-to-user',
        );
    }

    public function attachments(): array
    {
        if ($this->quotation->quotation_pdf_path) {
            return [
                Attachment::fromStorageDisk('local', $this->quotation->quotation_pdf_path)
                    ->as('Quotation_' . $this->quotation->purchase_id . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
