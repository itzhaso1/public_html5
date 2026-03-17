<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * @param array<string,mixed> $payload
     */
    public function sendPayload(array $payload): bool
    {
        $apiKey = trim((string) config('services.sendgrid.key'));
        if ($apiKey === '') {
            Log::warning('SendGrid API key is missing.');
            return false;
        }

        $endpoint = trim((string) config('services.sendgrid.endpoint', 'https://api.sendgrid.com/v3/mail/send'));
        if ($endpoint === '') {
            $endpoint = 'https://api.sendgrid.com/v3/mail/send';
        }

        $timeout = (int) config('services.sendgrid.timeout', 20);
        if ($timeout <= 0) {
            $timeout = 20;
        }

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->withToken($apiKey)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('SendGrid API rejected email payload', [
                'status' => $response->status(),
                'body' => (string) $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('SendGrid API request failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }
}

