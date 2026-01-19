<?php

namespace FriendsOfBotble\VietnamBankQr\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VietQRService
{
    private const DEFAULT_CACHE_PREFIX = 'vietqr_banks_';
    private const DEFAULT_CACHE_DURATION = 300;
    private const DEFAULT_TIMEOUT = 5;

    /**
     * Get list of banks from VietQR API.
     *
     * This method fetches bank data from VietQR public API,
     * normalizes the response to snake_case format for legacy compatibility,
     * and caches the result to reduce external API calls.
     *
     * ---
     * API Endpoint:
     *   GET https://api.vietqr.io/v2/banks
     *
     * Response example (simplified):
     * ```json
     * {
     *   "code": "00",
     *   "desc": "Success",
     *   "data": [
     *     {
     *       "name": "Ngân hàng TMCP An Bình",
     *       "code": "ABB",
     *       "bin": "970425",
     *       "shortName": "ABBANK",
     *       "logo": "https://cdn.vietqr.io/img/ABB.png",
     *       "swiftCode": "ABBVVNVX",
     *       "isTransfer": 1
     *     }
     *   ]
     * }
     * ```
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException When API request fails
     *
     * @docs https://www.vietqr.io/danh-sach-api/api-danh-sach-ma-ngan-hang
     */
    public static function getBanks(): array
    {
        $cacheKey = static::cachePrefix().'list';

        return Cache::remember($cacheKey, static::cacheDuration(), function () {
            try {
                $response = Http::timeout(static::timeout())
                    ->acceptJson()
                    ->get('https://api.vietqr.io/v2/banks');
            } catch (\Throwable $exception) {
                throw new RuntimeException('Unable to fetch VietQR bank list.', 0, $exception);
            }

            if (! $response->successful()) {
                throw new RuntimeException('Unable to fetch VietQR bank list.');
            }

            $code = $response->json('code');
            $banks = $response->json('data');

            if ($code !== '00' || ! is_array($banks)) {
                throw new RuntimeException('Invalid response from VietQR bank list API.');
            }

            return array_map(static function (array $bank): array {
                return [
                    'name' => $bank['name'] ?? null,
                    'code' => $bank['code'] ?? null,
                    'bin' => $bank['bin'] ?? null,
                    'short_name' => $bank['shortName'] ?? null,
                    'logo' => $bank['logo'] ?? null,
                    'swift_code' => $bank['swiftCode'] ?? null,
                    'is_transfer' => $bank['isTransfer'] ?? null,
                ];
            }, $banks);
        });
    }

    private static function cachePrefix(): string
    {
        return (string) config('plugins.fob-vietnam-bank-qr.vietqr.cache_prefix', static::DEFAULT_CACHE_PREFIX);
    }

    private static function cacheDuration(): int
    {
        return (int) config('plugins.fob-vietnam-bank-qr.vietqr.cache_duration', static::DEFAULT_CACHE_DURATION);
    }

    private static function timeout(): int
    {
        return (int) config('plugins.fob-vietnam-bank-qr.vietqr.request_timeout', static::DEFAULT_TIMEOUT);
    }
}
