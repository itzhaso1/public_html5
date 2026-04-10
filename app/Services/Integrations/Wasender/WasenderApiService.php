<?php

namespace App\Services\Integrations\Wasender;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WasenderApiService
{
    public function sendMessage(string $to, string $text): bool
    {
        $enabled = (bool) config('services.wasender.enabled', false);
        $apiKey = (string) config('services.wasender.api_key', '');
        $baseUrl = rtrim((string) config('services.wasender.base_url', 'https://www.wasenderapi.com/api'), '/');

        if (! $enabled || $apiKey === '') {
            return false;
        }

        $to = $this->normalizeTo($to);
        $text = trim($text);
        if ($to === '' || $text === '') {
            return false;
        }

        $maxAttempts = (int) config('services.wasender.max_attempts', 3);
        if ($maxAttempts < 1) $maxAttempts = 1;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                /** @var Response $res */
                $res = Http::withToken($apiKey)
                    ->acceptJson()
                    ->asJson()
                    ->timeout(15)
                    ->post($baseUrl . '/send-message', [
                        'to' => $to,
                        'text' => $text,
                    ]);

                // Rate limit: Wasender protection = 1 message / 5 seconds
                if ($res->status() === 429) {
                    $retryAfter = 5;
                    try {
                        $retryAfter = (int) ($res->json('retry_after') ?? 5);
                    } catch (\Throwable $e) {
                        $retryAfter = 5;
                    }
                    $retryAfter = max(1, min(10, $retryAfter));

                    Log::warning('Wasender rate limited (429)', [
                        'to' => $to,
                        'attempt' => $attempt,
                        'retry_after' => $retryAfter,
                    ]);

                    if ($attempt < $maxAttempts) {
                        // Give it a small buffer beyond retry_after
                        sleep($retryAfter + 1);
                        continue;
                    }

                    return false;
                }

                if ($res->successful()) {
                    $json = $res->json();
                    if (is_array($json) && array_key_exists('success', $json) && $json['success'] === false) {
                        Log::warning('Wasender send-message responded with success=false', [
                            'to' => $to,
                            'status' => $res->status(),
                            'body' => $json,
                        ]);
                        return false;
                    }
                    return true;
                }

                Log::warning('Wasender send-message failed', [
                    'to' => $to,
                    'attempt' => $attempt,
                    'status' => $res->status(),
                    'body' => $res->json() ?? $res->body(),
                ]);

                // Retry transient 5xx errors
                if ($attempt < $maxAttempts && $res->serverError()) {
                    usleep(250000 * $attempt);
                    continue;
                }

                return false;
            } catch (\Throwable $e) {
                Log::warning('Wasender send-message exception', [
                    'to' => $to,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                // Backoff and retry for transient errors
                if ($attempt < $maxAttempts) {
                    usleep(250000 * $attempt);
                    continue;
                }

                return false;
            }
        }

        return false;
    }

    private function normalizeTo(string $to): string
    {
        $to = trim((string) $to);
        if ($to === '') return '';

        // Allow group/community JIDs as-is (per Wasender docs).
        if (str_contains($to, '@')) {
            return $to;
        }

        $digits = preg_replace('/\D+/', '', $to) ?: '';
        if ($digits === '') return '';

        // Some users provide 00-prefix international format.
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Wasender expects E.164 formatted numbers (leading "+").
        return '+' . $digits;
    }
}

