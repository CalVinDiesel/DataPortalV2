<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseQuotation;

class TilesReadyNotification extends Mailable
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
            subject: '[3DHub] Your 3D Model Tiles Are Ready - ' . $this->quotation->purchase_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tiles-ready-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
