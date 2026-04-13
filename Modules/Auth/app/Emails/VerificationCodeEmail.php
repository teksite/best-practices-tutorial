<?php

namespace Modules\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificationCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $code ,public string $expireAt) {}

    /**
     * Build the message.
     */


    public function envelope() :Envelope
    {
     return new Envelope(
        subject: trans('auth::messages.verification_code.email_subject'),
     );
    }

    public function Content() : Content
    {
        return new Content(
            markdown: "auth::mails.verification-code",
            with: [
                'code' => $this->code,
                'expireAt' => $this->expireAt,
            ]
        );
    }
}
