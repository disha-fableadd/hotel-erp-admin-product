<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->connectWithRetry();

        return $next($request);
    }

    private function connectWithRetry(): void
    {
        $last = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                DB::connection()->getPdo();

                return;
            } catch (\Throwable $e) {
                $last = $e;

                if (! $this->isTransientConnectionError($e) || $attempt >= 2) {
                    throw $e;
                }

                DB::purge();
                DB::reconnect();
                usleep(150000);
            }
        }

        if ($last !== null) {
            throw $last;
        }
    }

    private function isTransientConnectionError(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '2002')
            || str_contains($message, 'Operation not permitted')
            || str_contains($message, 'Connection refused')
            || str_contains($message, 'gone away');
    }
}
