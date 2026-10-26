<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RemoteLicenseClient
{
    public function __construct(
        private readonly LicenseServerService $server
    ) {}

    public function isConfigured(): bool
    {
        return config('license.mode') === 'remote'
            && config('license.server_url') !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function activate(array $data): array
    {
        return $this->post('/activate', $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(array $data): array
    {
        return $this->post('/verify', $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function addDomain(array $data): array
    {
        return $this->post('/add-domain', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function post(string $path, array $data): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Remote license server is not configured.');
        }

        if ($this->shouldUseInternal()) {
            return $this->postInternal($path, $data);
        }

        $url = config('license.server_url').'/api/v1/license'.$path;

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders($this->headers())
            ->post($url, $data);

        $body = $response->json();
        if (is_array($body) && array_key_exists('success', $body)) {
            return $body;
        }

        if ($response->failed()) {
            $message = is_array($body) ? ($body['message'] ?? null) : null;
            $message = is_string($message) ? $message : $response->body();

            throw new RuntimeException($message !== '' ? $message : 'License server request failed.');
        }

        if (! is_array($body)) {
            throw new RuntimeException('Invalid license server response.');
        }

        return $body;
    }

    /**
     * Same-app / local dev: avoid HTTP to self (php artisan serve is single-threaded).
     *
     * @return array<string, mixed>
     */
    private function postInternal(string $path, array $data): array
    {
        $result = match ($path) {
            '/activate' => $this->server->activate($data),
            '/verify' => $this->server->verify($data),
            '/add-domain' => $this->server->addDomain($data),
            default => throw new RuntimeException('Unknown license API path.'),
        };

        return $result;
    }

    /**
     * In-process API only on the license server host or local loopback dev.
     */
    public function shouldUseInternal(): bool
    {
        if (config('license.use_internal', true) === false) {
            return false;
        }

        if (config('license.admin_enabled', false)) {
            return $this->licenseServerMatchesThisInstall();
        }

        if ($this->isLoopbackDevelopment()) {
            return $this->licenseServerMatchesThisInstall();
        }

        return false;
    }

    private function licenseServerMatchesThisInstall(): bool
    {
        $server = $this->normalizeBaseUrl((string) config('license.server_url'));
        if ($server === '') {
            return false;
        }

        $candidates = array_filter([
            $this->normalizeBaseUrl((string) config('app.url')),
            $this->currentRequestBaseUrl(),
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && $this->urlsPointToSameApp($server, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function isLoopbackDevelopment(): bool
    {
        $hosts = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            app()->runningInConsole() ? null : request()->getHost(),
        ]);

        foreach ($hosts as $host) {
            if ($host && $this->isLoopbackHost((string) $host)) {
                return true;
            }
        }

        return false;
    }

    private function currentRequestBaseUrl(): string
    {
        if (app()->runningInConsole()) {
            return '';
        }

        try {
            return $this->normalizeBaseUrl(request()->getSchemeAndHttpHost());
        } catch (\Throwable) {
            return '';
        }
    }

    private function urlsPointToSameApp(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $hostA = parse_url($a, PHP_URL_HOST);
        $hostB = parse_url($b, PHP_URL_HOST);
        $portA = (int) (parse_url($a, PHP_URL_PORT) ?? $this->defaultPort($a));
        $portB = (int) (parse_url($b, PHP_URL_PORT) ?? $this->defaultPort($b));

        if ($portA !== $portB) {
            return false;
        }

        if ($this->isLoopbackHost((string) $hostA) && $this->isLoopbackHost((string) $hostB)) {
            return true;
        }

        return false;
    }

    private function isLoopbackHost(string $host): bool
    {
        $host = strtolower($host);

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }

    private function defaultPort(string $url): int
    {
        return (parse_url($url, PHP_URL_SCHEME) ?? 'http') === 'https' ? 443 : 80;
    }

    private function normalizeBaseUrl(string $url): string
    {
        $url = strtolower(rtrim(trim($url), '/'));
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'http';

        if (! $host) {
            return '';
        }

        $defaultPort = $scheme === 'https' ? 443 : 80;
        if ($port === null || (int) $port === $defaultPort) {
            return $scheme.'://'.$host;
        }

        return $scheme.'://'.$host.':'.$port;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [];

        $apiKey = (string) config('license.server_api_key');
        if ($apiKey !== '') {
            $headers['X-License-Api-Key'] = $apiKey;
        }

        return $headers;
    }
}

