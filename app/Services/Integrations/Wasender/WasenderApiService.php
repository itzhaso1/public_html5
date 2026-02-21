<?php

namespace App\Services\Integrations\Wasender;

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

        try {
            $res = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->retry(2, 250)
                ->post($baseUrl . '/send-message', [
                    'to' => $to,
                    'text' => $text,
                ]);

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
                'status' => $res->status(),
                'body' => $res->json() ?? $res->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Wasender send-message exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
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

