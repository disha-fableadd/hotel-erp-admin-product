<?php

namespace App\Services\License;

use RuntimeException;

class LicenseSigner
{
    public function secret(): string
    {
        $secret = (string) config('license.secret');

        if (strlen($secret) < 32) {
            throw new RuntimeException('LICENSE_SECRET must be at least 32 characters.');
        }

        return $secret;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function sign(array $payload): string
    {
        return hash_hmac('sha256', $this->canonicalJson($payload), $this->secret());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verify(array $payload, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        return hash_equals($this->sign($payload), $signature);
    }

    public function encodeToken(array $payload): string
    {
        $signing = $this->signingPayload($payload);
        $signature = $this->sign($signing);
        $body = $this->base64UrlEncode($this->canonicalJson($signing));

        return $body.'.'.$this->base64UrlEncode($signature);
    }

    /**
     * Fields excluded from wire-token HMAC (stored separately on disk).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function signingPayload(array $payload): array
    {
        return array_filter(
            $payload,
            fn ($key) => ! in_array($key, ['signature', 'installation_hash', 'stored_at'], true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    public function decodeToken(string $token): array
    {
        $parts = explode('.', trim($token));
        if (count($parts) !== 2) {
            throw new RuntimeException('Invalid license token format.');
        }

        $payload = json_decode($this->base64UrlDecode($parts[0]), true);
        if (! is_array($payload)) {
            throw new RuntimeException('Invalid license token payload.');
        }

        $signature = $this->base64UrlDecode($parts[1]);

        return [$payload, $signature];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function canonicalJson(array $payload): string
    {
        $normalized = $this->ksortRecursive($payload);

        return json_encode($normalized, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function ksortRecursive(array $data): array
    {
        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->ksortRecursive($value);
            }
        }

        return $data;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid license token encoding.');
        }

        return $decoded;
    }
}

