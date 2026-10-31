<?php

namespace App\Support\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Locale;
use Throwable;

/**
 * Resolves a visitor's country and city. See the `geo` option in config/analytics.php.
 */
class Geo
{
    /** @return array{country_code: ?string, country: ?string, city: ?string} */
    public static function locate(Request $request): array
    {
        $empty = ['country_code' => null, 'country' => null, 'city' => null];
        $mode = config('analytics.geo');

        if ($mode === 'none') {
            return $empty;
        }

        $code = strtoupper((string) $request->header('CF-IPCountry'));

        if (preg_match('/^[A-Z]{2}$/', $code) && ! in_array($code, ['XX', 'T1'])) {
            return [
                'country_code' => $code,
                'country' => static::countryName($code),
                'city' => $request->header('CF-IPCity') ?: null,
            ];
        }

        $ip = $request->ip();

        if ($mode !== 'ip-api' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $empty;
        }

        return Cache::remember('analytics.geo.'.hash('sha256', $ip), now()->addDay(), function () use ($ip, $empty) {
            try {
                $data = Http::timeout(2)
                    ->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,countryCode,city'])
                    ->json();
            } catch (Throwable) {
                return $empty;
            }

            if (($data['status'] ?? null) !== 'success') {
                return $empty;
            }

            return [
                'country_code' => $data['countryCode'],
                'country' => static::countryName($data['countryCode']),
                'city' => $data['city'] ?: null,
            ];
        });
    }

    public static function countryName(string $code): string
    {
        return Locale::getDisplayRegion('-'.$code, 'en') ?: $code;
    }
}
