<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HtmlMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $subjectLine,
        private readonly string $bodyText,
    ) {
        //
    }

    public function build(): self
    {
        return $this
            ->subject($this->subjectLine)
            ->view('emails.notification', [
                'subject' => $this->subjectLine,
                'content' => $this->bodyText,
            ])
            ->text('emails.notification_plain', [
                'subject' => $this->subjectLine,
                'content' => $this->bodyText,
            ]);
    }
}

