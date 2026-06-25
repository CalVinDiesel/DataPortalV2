<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Inquiry;
use Illuminate\Mail\Mailables\Attachment;

class InquirySentToUser extends Mailable
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
            subject: '[3DHub] Your Inquiry Quotation - ' . $this->inquiry->inquiry_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-sent-to-user',
        );
    }

    public function attachments(): array
    {
        if ($this->inquiry->quotation_pdf_path) {
            return [
                Attachment::fromStorageDisk('local', $this->inquiry->quotation_pdf_path)
                    ->as('Quotation_' . $this->inquiry->inquiry_id . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
