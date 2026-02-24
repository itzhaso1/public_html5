<?php

namespace App\Support\Email;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotifier
{
    /**
     * @param string|array<int,string> $to
     */
    public static function send(string|array $to, string $subject, string $text): bool
    {
        $enabled = (bool) config('services.email_notify.enabled', false);
        if (! $enabled) return false;

        $subject = trim($subject);
        $text = trim($text);
        if ($subject === '' || $text === '') return false;

        $emails = is_array($to) ? $to : [$to];
        $emails = array_values(array_unique(array_filter(array_map(function ($e) {
            $e = trim((string) $e);
            return filter_var($e, FILTER_VALIDATE_EMAIL) ? $e : null;
        }, $emails))));

        if (empty($emails)) return false;

        try {
            Mail::raw($text, function ($message) use ($emails, $subject) {
                $message->to($emails)->subject($subject);
            });
            return true;
        } catch (\Throwable $e) {
            Log::warning('EmailNotifier send exception', [
                'to' => $emails,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @param string|array<int,string> $to
     */
    public static function sendAfterCommit(string|array $to, string $subject, string $text): void
    {
        try {
            if (DB::transactionLevel() > 0) {
                DB::afterCommit(function () use ($to, $subject, $text) {
                    self::send($to, $subject, $text);
                });
                return;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        self::send($to, $subject, $text);
    }
}

