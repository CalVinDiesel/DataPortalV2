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
    public bool $isUpdate;

    public function __construct(Inquiry $inquiry, bool $isUpdate = false)
    {
        $this->inquiry = $inquiry;
        $this->isUpdate = $isUpdate;
    }

    public function envelope(): Envelope
    {
        $subject = $this->isUpdate
            ? '[3DHub] Inquiry Request Updated - ' . $this->inquiry->inquiry_id
            : '[3DHub] Inquiry Request Received - ' . $this->inquiry->inquiry_id;

        return new Envelope(
            subject: $subject,
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
