<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AboutContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderEmail,
        public string $messageBody,
        public string $tenantName,
    ) {}

    public function build(): self
    {
        return $this->replyTo($this->senderEmail)
            ->subject("رسالة تواصل من {$this->tenantName}")
            ->view('emails.about-contact');
    }
}
