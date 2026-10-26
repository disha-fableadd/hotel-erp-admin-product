<?php

namespace App\Services\License;

class DomainNormalizer
{
    public static function normalize(?string $host): ?string
    {
        if ($host === null || trim($host) === '') {
            return null;
        }

        $host = strtolower(trim($host));
        $host = preg_replace('#^https?://#', '', $host) ?? $host;
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host !== '' ? $host : null;
    }

    public static function fromRequest(): ?string
    {
        $urlHost = parse_url(config('app.url', ''), PHP_URL_HOST);

        return self::normalize(request()->getHost() ?: $urlHost);
    }

    public static function isLocalhost(?string $domain): bool
    {
        if ($domain === null) {
            return false;
        }

        return in_array($domain, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($domain, '.localhost')
            || str_ends_with($domain, '.test')
            || str_ends_with($domain, '.local');
    }

    public static function domainsMatch(string $allowed, string $current): bool
    {
        $allowed = self::normalize($allowed);
        $current = self::normalize($current);

        if ($allowed === null || $current === null) {
            return false;
        }

        if ($allowed === $current) {
            return true;
        }

        return str_ends_with($current, '.'.$allowed);
    }
}

