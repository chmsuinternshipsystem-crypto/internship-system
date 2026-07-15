<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HteEvaluationLinkMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly string $studentName,
        public readonly ?string $companyName,
        public readonly string $transactionUrl,
        public readonly ?CarbonInterface $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('OJT Evaluation Request'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hte-evaluation-link',
        );
    }
}
