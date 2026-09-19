<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class LeadSubmittedMail extends Mailable
{
    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'طلب خدمة جديد من '.$this->lead->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.lead-submitted',
        );
    }
}
