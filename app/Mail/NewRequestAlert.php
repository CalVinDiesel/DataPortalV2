<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewRequestAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $userEmail;
    public $companyName;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $userEmail, $companyName = null, $reason = null)
    {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->companyName = $companyName;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Access Request: ' . $this->userName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-request-alert',
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
