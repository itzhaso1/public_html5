<?php

namespace App\Support\Email;

use App\Mail\HtmlMessageMail;
use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotifier
{
    /**
     * Defer sending until app termination (after HTTP response), when possible.
     * Falls back to immediate send in console/edge cases.
     *
     * @param string|array<int,string> $to
     */
    private static function sendDeferred(string|array $to, string $subject, string $text): void
    {
        try {
            if (! app()->runningInConsole()) {
                app()->terminating(function () use ($to, $subject, $text) {
                    self::send($to, $subject, $text);
                });
                return;
            }
        } catch (\Throwable $e) {
            // fall through to sync send
        }

        self::send($to, $subject, $text);
    }

    /**
     * Collect admin recipient emails from config + admins + settings email.
     *
     * @return array<int,string>
     */
    public static function adminRecipients(): array
    {
        $emails = [];

        // Explicit config list (highest priority)
        foreach ((array) config('services.email_notify.admin_to', []) as $e) {
            $e = trim((string) $e);
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $e;
            }
        }

        // Fallback: all admins in DB
        try {
            foreach ((array) Admin::query()->pluck('email')->all() as $e) {
                $e = trim((string) $e);
                if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $e;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Fallback: main settings email
        try {
            $s = Cache::get('app_settings') ?: Setting::query()->latest('id')->first();
            $e = trim((string) ($s?->email ?? ''));
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $e;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return array_values(array_unique($emails));
    }

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
            Mail::to($emails)->send(new HtmlMessageMail($subject, $text));
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
                    self::sendDeferred($to, $subject, $text);
                });
                return;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        self::sendDeferred($to, $subject, $text);
    }
}

