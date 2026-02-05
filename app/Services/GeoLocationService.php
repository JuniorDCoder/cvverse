<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Get the country code for the given IP address.
     *
     * @return string|null Two-letter country code (ISO 3166-1 alpha-2)
     */
    public function getCountryCode(string $ip): ?string
    {
        // 0. Force default country from ENV (useful for dev/testing)
        if (app()->isLocal() && config('app.default_country')) {
            return config('app.default_country');
        }

        // 1. Cloudflare header (if behind CF)
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            return $_SERVER['HTTP_CF_IPCOUNTRY'];
        }

        // 2. Check header for real IP if behind other proxies
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // The first IP in the list is the original client
            $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $realIp = trim($forwardedIps[0]);
            if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                $ip = $realIp;
            }
        }

        // 3. Skip local IPs or Private Networks
        if (
            $ip === '127.0.0.1' ||
            $ip === '::1' ||
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            // Return null or default country if specified
            return config('app.default_country');
        }

        // 4. External API (ip-api.com is free for non-commercial, but rate limited.
        // For production, consider a paid service or a local MaxMind database)
        return Cache::remember("geo_ip_{$ip}", 86400, function () use ($ip) {
            try {
                // Use HTTPS if possible, though ip-api free is http only usually unless paid
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    return $response->json('countryCode');
                }
            } catch (\Exception $e) {
                Log::warning("GeoLocationService failed for IP {$ip}: ".$e->getMessage());
            }

            return null;
        });
    }
}
