<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPortalOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $studentName,
        public readonly string $otpCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your student portal login code'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-portal-otp',
        );
    }
}
