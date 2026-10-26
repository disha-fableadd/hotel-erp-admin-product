<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class LicenseStorage
{
    private const DIRECTORY = 'app/.license';

    private const FILE = 'payload.enc';

    /** @var 'missing'|'corrupt'|null */
    private ?string $lastReadFailure = null;

    public function path(): string
    {
        return storage_path(self::DIRECTORY.'/'.self::FILE);
    }

    public function exists(): bool
    {
        return File::isFile($this->path());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function write(array $payload): void
    {
        $directory = storage_path(self::DIRECTORY);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0750, true);
        }

        $payload['installation_hash'] = $this->installationHash();
        $payload['stored_at'] = time();

        $written = File::put($this->path(), Crypt::encryptString(json_encode($payload)));
        @chmod($this->path(), 0640);

        if ($written === false || ! $this->exists()) {
            throw new \RuntimeException(
                'Could not save license file. Ensure storage/app/.license is writable by the web server.'
            );
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(): ?array
    {
        $this->lastReadFailure = null;

        if (! $this->exists()) {
            $this->lastReadFailure = 'missing';

            return null;
        }

        try {
            $json = Crypt::decryptString(File::get($this->path()));
            $payload = json_decode($json, true);

            if (! is_array($payload)) {
                $this->lastReadFailure = 'corrupt';

                return null;
            }

            return $payload;
        } catch (\Throwable) {
            $this->lastReadFailure = 'corrupt';

            return null;
        }
    }

    /**
     * Why read() last returned null: missing file or decrypt/parse failure (often APP_KEY change).
     *
     * @return 'missing'|'corrupt'|null
     */
    public function readFailure(): ?string
    {
        return $this->lastReadFailure;
    }

    public function delete(): void
    {
        if ($this->exists()) {
            File::delete($this->path());
        }
    }

    public function installationHash(): string
    {
        $key = (string) config('app.key');

        return hash('sha256', $key.'|'.config('license.product_code'));
    }
}
