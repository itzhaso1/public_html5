<?php

namespace App\Services\Integrations\Shop2TopUp;

use Illuminate\Support\Facades\Http;

class Shop2TopUpService
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $apiKey = null,
    ) {
        //
    }

    private function baseUrl(): string
    {
        return rtrim($this->baseUrl ?? (string) config('services.shop2topup.base_url'), '/');
    }

    private function apiKey(): string
    {
        return (string) ($this->apiKey ?? config('services.shop2topup.api_key'));
    }

    /**
     * @return array{success:bool, offers:array<int, array{name:string,itemId:int,price:string}>, msg?:string}
     */
    public function getOffers(): array
    {
        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'offers' => [], 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->get($this->baseUrl() . '/offers');
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'offers' => [], 'msg' => 'تعذر الاتصال بالمزود'];
        }

        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'offers' => [],
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'offers' => [], 'msg' => 'رد غير صالح من المزود'];
        }

        return [
            'success' => (bool) ($data['success'] ?? false),
            'offers' => (array) ($data['offers'] ?? []),
            'msg' => (string) ($data['msg'] ?? ''),
        ];
    }

    /**
     * @return array{success:bool, balance?:string, msg?:string}
     */
    public function getBalance(): array
    {
        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->get($this->baseUrl() . '/balance');
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'msg' => 'تعذر الاتصال بالمزود'];
        }

        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'msg' => 'رد غير صالح من المزود'];
        }

        return [
            'success' => (bool) ($data['success'] ?? false),
            'balance' => isset($data['balance']) ? (string) $data['balance'] : null,
            'msg' => (string) ($data['msg'] ?? ''),
        ];
    }

    /**
     * @return array{success:bool, status?:string, player_id?:string, player_name?:string, time?:string, delivery_at?:string, offer?:string, secure_id?:string, order_id?:string, msg?:string}
     */
    public function getTransaction(string $trxId): array
    {
        $trxId = trim($trxId);
        if ($trxId === '') {
            return ['success' => false, 'msg' => 'TRXID_MISSING'];
        }

        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->post($this->baseUrl() . '/transaction', [
                    'trx_id' => $trxId,
                ]);
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'msg' => 'تعذر الاتصال بالمزود'];
        }

        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'msg' => 'رد غير صالح من المزود'];
        }

        return array_merge(['success' => (bool) ($data['success'] ?? false)], $data);
    }

    /**
     * Check player name/region for a given playerID.
     *
     * @return array{success:bool, player_name?:string, region?:string, msg?:string}
     */
    public function checkPlayer(string $playerId): array
    {
        $playerId = trim($playerId);
        if (mb_strlen($playerId) < 3) {
            return ['success' => false, 'msg' => 'WRONG_ID'];
        }

        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        // Step 1: send request to check player name
        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->post($this->baseUrl() . '/id', [
                    'playerID' => $playerId,
                ]);
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'msg' => 'تعذر الاتصال بالمزود'];
        }

        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'msg' => 'رد غير صالح من المزود'];
        }

        $normalized = [
            'success' => (bool) ($data['success'] ?? false),
            'player_name' => isset($data['player_name']) ? (string) $data['player_name'] : null,
            'region' => isset($data['region']) ? (string) $data['region'] : null,
            'msg' => isset($data['msg']) ? (string) $data['msg'] : null,
        ];

        // If player name already returned, we're done.
        if ($normalized['success'] === true && !empty($normalized['player_name'])) {
            return $normalized;
        }

        // Step 2: fetch result (may be NOT_READY)
        // This is important because POST /id can return DUPLICATE_TASK or just accept the task.
        $result = $this->getPlayerNameResult($playerId);

        // Prefer the resolved player_name from GET when available.
        if (($result['success'] ?? false) === true && !empty($result['player_name'])) {
            return [
                'success' => true,
                'player_name' => $result['player_name'],
                'region' => $normalized['region'] ?? null,
                'msg' => null,
            ];
        }

        // Bubble the latest msg (NOT_READY / WRONG_ID / WRONG_REGION / ...)
        return [
            'success' => false,
            'msg' => $result['msg'] ?? ($normalized['msg'] ?? 'NOT_READY'),
        ];
    }

    /**
     * Get player name request result.
     *
     * @return array{success:bool, player_name?:string, msg?:string}
     */
    public function getPlayerNameResult(string $playerId): array
    {
        $playerId = trim($playerId);
        if (mb_strlen($playerId) < 3) {
            return ['success' => false, 'msg' => 'WRONG_ID'];
        }

        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->get($this->baseUrl() . '/id', [
                    'playerID' => $playerId,
                ]);
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'msg' => 'تعذر الاتصال بالمزود'];
        }

        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'msg' => 'رد غير صالح من المزود'];
        }

        if (($data['success'] ?? false) === true) {
            return [
                'success' => true,
                'player_name' => isset($data['player_name']) ? (string) $data['player_name'] : null,
                'msg' => null,
            ];
        }

        return [
            'success' => false,
            'msg' => isset($data['msg']) ? (string) $data['msg'] : 'NOT_READY',
        ];
    }

    /**
     * Create a topup request.
     *
     * @return array{success:bool, trxID?:string, msg?:string}
     */
    public function topup(string $playerId, int $offerId, string $trxId): array
    {
        $playerId = trim($playerId);
        $trxId = trim($trxId);

        if ($playerId === '' || mb_strlen($playerId) < 3) {
            return ['success' => false, 'msg' => 'WRONG_ID'];
        }
        if ($offerId <= 0) {
            return ['success' => false, 'msg' => 'OFFER_MISSING'];
        }
        if ($trxId === '') {
            return ['success' => false, 'msg' => 'MISS_TRX'];
        }

        $key = $this->apiKey();
        if ($key === '') {
            return ['success' => false, 'msg' => 'SHOP2TOPUP_API_KEY غير مضبوط'];
        }

        $timeout = (int) config('services.shop2topup.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])
                ->post($this->baseUrl() . '/topup', [
                    'playerID' => $playerId,
                    'offer' => $offerId,
                    'trx_id' => $trxId,
                ]);
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'msg' => 'تعذر الاتصال بالمزود'];
        }

        // They may return 403 DUPLICATE_TASK; treat as failure with msg.
        if (! $response->successful()) {
            $json = $response->json();
            $msg = is_array($json) ? ($json['msg'] ?? null) : null;
            return [
                'success' => false,
                'msg' => $msg ?: ('HTTP ' . $response->status()),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['success' => false, 'msg' => 'رد غير صالح من المزود'];
        }

        $trx = null;
        if (array_key_exists('trxID', $data)) {
            $trx = $data['trxID'];
        } elseif (array_key_exists('trxId', $data)) {
            $trx = $data['trxId'];
        } elseif (array_key_exists('trx_id', $data)) {
            $trx = $data['trx_id'];
        }

        return [
            'success' => (bool) ($data['success'] ?? false),
            'trxID' => isset($trx) ? (string) $trx : null,
            'msg' => isset($data['msg']) ? (string) $data['msg'] : null,
        ];
    }
}

