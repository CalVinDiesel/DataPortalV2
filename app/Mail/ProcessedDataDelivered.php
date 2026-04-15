<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProcessedDataDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public $upload;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\ClientUpload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Processed Data is Ready: ' . $this->upload->project_title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.processed-delivered',
            with: [
                'upload' => $this->upload,
                'method' => $this->upload->delivery_method,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
