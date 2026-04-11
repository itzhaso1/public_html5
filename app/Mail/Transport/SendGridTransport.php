<?php

namespace App\Mail\Transport;

use App\Services\SendGridService;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class SendGridTransport extends AbstractTransport
{
    public function __construct(
        private readonly SendGridService $sendGrid,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        if (! $original instanceof Email) {
            throw new TransportException('SendGrid transport supports Symfony Email messages only.');
        }

        $from = $original->getFrom()[0] ?? null;
        if (! $from instanceof Address) {
            throw new TransportException('Email "from" address is missing.');
        }

        $to = $this->mapAddresses($original->getTo());
        if (empty($to)) {
            $to = array_map(function ($recipient) {
                return ['email' => (string) $recipient->getAddress()];
            }, $message->getEnvelope()->getRecipients());
        }
        if (empty($to)) {
            throw new TransportException('No recipients provided for email.');
        }

        $personalization = ['to' => $to];
        $cc = $this->mapAddresses($original->getCc());
        if (! empty($cc)) {
            $personalization['cc'] = $cc;
        }
        $bcc = $this->mapAddresses($original->getBcc());
        if (! empty($bcc)) {
            $personalization['bcc'] = $bcc;
        }

        $subject = trim((string) ($original->getSubject() ?? ''));
        $htmlBody = $original->getHtmlBody();
        $textBody = $original->getTextBody();

        if ($htmlBody === null && $textBody === null) {
            $textBody = ' ';
        }

        $content = [];
        if ($textBody !== null) {
            $content[] = ['type' => 'text/plain', 'value' => (string) $textBody];
        }
        if ($htmlBody !== null) {
            $content[] = ['type' => 'text/html', 'value' => (string) $htmlBody];
        }

        $payload = [
            'personalizations' => [$personalization],
            'from' => $this->formatAddress($from),
            'subject' => $subject,
            'content' => $content,
        ];

        $replyTo = $original->getReplyTo()[0] ?? null;
        if ($replyTo instanceof Address) {
            $payload['reply_to'] = $this->formatAddress($replyTo);
        }

        $attachments = $this->mapAttachments($original);
        if (! empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        if (! $this->sendGrid->sendPayload($payload)) {
            throw new TransportException('SendGrid API failed to send message.');
        }
    }

    public function __toString(): string
    {
        return 'sendgrid';
    }

    /**
     * @param array<int,Address> $addresses
     * @return array<int,array<string,string>>
     */
    private function mapAddresses(array $addresses): array
    {
        return array_values(array_filter(array_map(function (Address $address) {
            $email = trim($address->getAddress());
            if ($email === '') {
                return null;
            }

            $out = ['email' => $email];
            $name = trim((string) $address->getName());
            if ($name !== '') {
                $out['name'] = $name;
            }
            return $out;
        }, $addresses)));
    }

    /**
     * @return array<string,string>
     */
    private function formatAddress(Address $address): array
    {
        $formatted = ['email' => $address->getAddress()];
        $name = trim((string) $address->getName());
        if ($name !== '') {
            $formatted['name'] = $name;
        }

        return $formatted;
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function mapAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $part) {
            if (! $part instanceof DataPart) {
                continue;
            }

            $rawBody = method_exists($part, 'bodyToString') ? $part->bodyToString() : '';
            $attachments[] = array_filter([
                'content' => base64_encode((string) $rawBody),
                'type' => $part->getMediaType() . '/' . $part->getMediaSubtype(),
                'filename' => (string) ($part->getFilename() ?? 'attachment'),
                'disposition' => (string) ($part->getDisposition() ?? 'attachment'),
                'content_id' => $part->getContentId(),
            ], function ($value) {
                return $value !== null && $value !== '';
            });
        }

        return $attachments;
    }
}

