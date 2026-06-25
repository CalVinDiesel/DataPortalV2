<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Inquiry;

class InquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public Inquiry $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[3DHub] Inquiry Request Received - ' . $this->inquiry->inquiry_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
