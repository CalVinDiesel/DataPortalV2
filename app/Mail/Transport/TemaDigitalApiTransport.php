<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TemaDigitalApiTransport extends AbstractTransport
{
    /**
     * Send the message using the HTTP API.
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        // Extract HTML body and Plain text body
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();
        
        $body = $html ?: $text;
        $isHtml = !empty($html);

        // Extract Recipient(s)
        $toAddresses = [];
        foreach ($email->getTo() as $address) {
            $toAddresses[] = $address->getAddress();
        }
        $to = implode(', ', $toAddresses);

        // Retrieve config or env values with proper fallbacks
        $apiKey = env('MAIL_API_KEY', 'temadigital');
        $apiUrl = env('MAIL_API_URL', 'https://mail-api.temadigital.my/send-email');
        
        // 👮 Sender details dynamically loaded from env configuration
        $fromAddress = env('MAIL_FROM_ADDRESS', 'noreply.temadigital@gmail.com');
        $fromName = env('MAIL_FROM_NAME', '3D Hub Data Portal');

        // Extract custom sender name from Laravel mailer if it exists
        $fromList = $email->getFrom();
        if (!empty($fromList)) {
            $fromObj = $fromList[0];
            $fromName = $fromObj->getName() ?: $fromName;
        }

        $subject = $email->getSubject();

        // 🚀 Dynamic Payload Alignment
        $payload = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'from' => $fromAddress,
            'fromName' => $fromName,
            'isHtml' => $isHtml,
        ];

        // Retrieve CC and BCC if they are set
        $ccAddresses = [];
        foreach ($email->getCc() as $address) {
            $ccAddresses[] = $address->getAddress();
        }
        if (!empty($ccAddresses)) {
            $payload['cc'] = implode(', ', $ccAddresses);
        }

        $bccAddresses = [];
        foreach ($email->getBcc() as $address) {
            $bccAddresses[] = $address->getAddress();
        }
        if (!empty($bccAddresses)) {
            $payload['bcc'] = implode(', ', $bccAddresses);
        }

        // Support reply-to header in the payload just in case the API processes it
        $replyToAddresses = [];
        foreach ($email->getReplyTo() as $address) {
            $replyToAddresses[] = $address->getAddress();
        }
        if (!empty($replyToAddresses)) {
            $payload['replyTo'] = implode(', ', $replyToAddresses);
        }

        Log::info("✉️ [TemaDigital API Mail] Sending email via HTTP API to: {$to}. Subject: {$subject}");

        // POST request to Plesk Email API
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($apiUrl, $payload);

        if (!$response->successful()) {
            Log::error("❌ [TemaDigital API Mail] HTTP request failed. Status: " . $response->status() . " Response: " . $response->body());
            throw new \Exception('Failed to send email through TemaDigital API: ' . $response->body());
        }

        Log::info("✅ [TemaDigital API Mail] HTTP request completed successfully.");
    }

    public function __toString(): string
    {
        return 'temadigital_api';
    }
}
