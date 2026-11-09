<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail over HTTPS through the Google Apps Script relay (scripts/mail-relay.gs).
 * Render's free plan blocks outgoing SMTP, so Gmail on port 587 cannot be reached from there.
 * The script sends from the Gmail account that deployed it and answers {"ok": true} once sent.
 */
class RelayTransport extends AbstractTransport
{
    public function __construct(private string $url, private string $secret, private int $timeout = 30)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $to = implode(',', array_map(fn (Address $address) => $address->getAddress(), $message->getEnvelope()->getRecipients()));
        $html = $email->getHtmlBody();
        if (!is_string($html) || $html === '') {
            $html = nl2br(e((string) $email->getTextBody()));
        }

        try {
            // Apps Script answers a POST with a redirect to the result, which is fetched with GET.
            $response = Http::timeout($this->timeout)
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->acceptJson()
                ->post($this->url, [
                    'secret' => $this->secret,
                    'to' => $to,
                    'subject' => (string) $email->getSubject(),
                    'html' => $html,
                    'name' => $email->getFrom()[0]?->getName() ?: config('mail.from.name', 'Cartzy'),
                ]);
        } catch (\Throwable $e) {
            throw new TransportException('Mail relay could not be reached: ' . $e->getMessage(), 0, $e);
        }

        if (!$response->successful() || $response->json('ok') !== true) {
            throw new TransportException('Mail relay did not send the email (HTTP ' . $response->status() . '): '
                . mb_substr((string) ($response->json('error') ?? $response->body()), 0, 300));
        }
    }

    public function __toString(): string
    {
        return 'relay';
    }
}
