<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class QuoteRequestMail extends Mailable
{
    public function __construct(
        public string $customerName,
        public string $phone,
        public string $customerEmail,
        public string $serviceType,
        public string $details,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                (string) config('mail.from.address'),
                (string) config('mail.from.name'),
            ),
            replyTo: [
                new Address($this->customerEmail, $this->customerName),
            ],
            subject: 'طلب عرض سعر جديد من '.$this->customerName.' - ركيزة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.quote-request',
        );
    }
}
