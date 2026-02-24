<?php

namespace App\Support\Shop2TopUp;

use App\Models\Product;

final class Shop2TopUpBundle
{
    /**
     * @return array<int,int> Offer IDs in order.
     */
    public static function offerIdsForProduct(?Product $product): array
    {
        return self::parseOfferIds((string) ($product?->itemID ?? ''));
    }

    /**
     * Parse product itemID into one or multiple offer IDs.
     *
     * Supports:
     * - "123"
     * - "2400+2400+210"
     * - "2400,2400,210"
     * - "[2400,2400,210]"
     *
     * @return array<int,int>
     */
    public static function parseOfferIds(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') return [];

        // JSON array
        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            try {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $out = [];
                    foreach ($decoded as $v) {
                        $id = (int) $v;
                        if ($id > 0) $out[] = $id;
                    }
                    return $out;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Split by common separators: whitespace, +, comma, pipe.
        $parts = preg_split('/[\s,\+\|]+/u', $raw) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '') continue;
            $id = (int) $p;
            if ($id > 0) $out[] = $id;
        }
        if (!empty($out)) return $out;

        $single = (int) $raw;
        return $single > 0 ? [$single] : [];
    }

    /**
     * Parse stored trx id value (string or JSON array) into list.
     *
     * @return array<int,string>
     */
    public static function parseTrxIds(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') return [];

        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            try {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $out = [];
                    foreach ($decoded as $v) {
                        $s = trim((string) $v);
                        if ($s !== '') $out[] = $s;
                    }
                    $out = array_values(array_unique($out));
                    if (!empty($out)) return $out;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Also accept comma/+ separated list (admin input/debug).
        if (strpbrk($raw, ',+|') !== false || str_contains($raw, "\n")) {
            $parts = preg_split('/[\s,\+\|\r\n]+/u', $raw) ?: [];
            $out = [];
            foreach ($parts as $p) {
                $p = trim((string) $p);
                if ($p !== '') $out[] = $p;
            }
            $out = array_values(array_unique($out));
            if (!empty($out)) return $out;
        }

        return [$raw];
    }

    /**
     * Encode trx ids list for storage in `shop2topup_trx_id` column.
     */
    public static function encodeTrxIds(array $ids): string
    {
        $ids = array_values(array_unique(array_filter(array_map(function ($v) {
            $s = trim((string) $v);
            return $s !== '' ? $s : null;
        }, $ids))));

        if (count($ids) <= 1) {
            return (string) ($ids[0] ?? '');
        }

        return (string) json_encode($ids, JSON_UNESCAPED_UNICODE);
    }

    public static function isDeliveredStatus(?string $status, ?string $msg = null): bool
    {
        $hay = strtoupper(trim((string) $status . ' ' . (string) $msg));
        if ($hay === '') return false;
        return str_contains($hay, 'DELIVER')
            || str_contains($hay, 'SUCCESS')
            || str_contains($hay, 'COMPLET')
            || str_contains($hay, 'DONE');
    }

    public static function isFailedStatus(?string $status, ?string $msg = null): bool
    {
        $hay = strtoupper(trim((string) $status . ' ' . (string) $msg));
        if ($hay === '') return false;
        return str_contains($hay, 'REFUND_REGION')
            || str_contains($hay, 'REFUND')
            || str_contains($hay, 'FAILED')
            || str_contains($hay, 'REJECT')
            || str_contains($hay, 'CANCEL')
            || str_contains($hay, 'ERROR');
    }

    /**
     * @param array<int, array<string,mixed>> $transactions
     * @return array{total:int, delivered:int, failed:int, is_delivered:bool, is_failed:bool, is_partial:bool}
     */
    public static function summarizeTransactions(array $transactions): array
    {
        $total = count($transactions);
        $delivered = 0;
        $failed = 0;
        foreach ($transactions as $trx) {
            $status = isset($trx['status']) ? (string) $trx['status'] : '';
            $msg = isset($trx['msg']) ? (string) $trx['msg'] : '';
            if (self::isDeliveredStatus($status, $msg)) $delivered++;
            if (self::isFailedStatus($status, $msg)) $failed++;
        }

        return [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'is_delivered' => $total > 0 && $delivered === $total,
            'is_failed' => $failed > 0,
            'is_partial' => $delivered > 0 && $delivered < $total,
        ];
    }
}

