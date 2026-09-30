<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data, public string $type)
    {
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'devis'
            ? 'Nouvelle demande de devis - ' . ($this->data['organisation'] ?? 'Site web')
            : 'Nouvelle demande de formation - ' . ($this->data['organisation'] ?? 'Site web');

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead',
            with: [
                'data' => $this->data,
                'type' => $this->type,
            ],
        );
    }
}