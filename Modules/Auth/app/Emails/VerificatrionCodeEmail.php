<?php

namespace Modules\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificatrionCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public string|int $code , public string $expiredAt)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'verification code' . '-' . config('app.name') . '-' . config('app.env'),

        );
    }

    public function content()
    {
        return new Content(
            markdown: 'mails.verification-code',
            with: [
                'code' => $this->code,
                'expiredAt'=>$this->expiredAt,
            ]
        );
    }


}
