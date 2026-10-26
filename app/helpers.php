<?php

if (! function_exists('image_path_base')) {
    /**
     * Base URL for public assets (always ends with /).
     */
    function image_path_base(): string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        try {
            if (function_exists('app') && app()->bound('config')) {
                $configured = config('app.image_path');
                if (is_string($configured) && $configured !== '') {
                    return $cached = rtrim($configured, '/').'/';
                }

                $appUrl = config('app.url');
                if (is_string($appUrl) && $appUrl !== '') {
                    return $cached = rtrim($appUrl, '/').'/';
                }
            }
        } catch (\Throwable) {
            // Application not booted yet.
        }

        $raw = env('ImagePath') ?: env('IMAGE_PATH') ?: env('APP_URL', '');

        return $cached = ($raw !== '' ? rtrim((string) $raw, '/').'/' : '/');
    }
}

if (! function_exists('image_path')) {
    /**
     * Build a full URL to a file under public/ (e.g. admin/assets/... or storage/...).
     */
    function image_path(?string $path = ''): string
    {
        $base = image_path_base();

        if ($path === null || $path === '') {
            return $base;
        }

        return $base.ltrim($path, '/');
    }
}

if (! function_exists('safe_has_column')) {
    /**
     * Safely check if a table has a column, falling back to a SELECT LIMIT 0 query
     * if Schema::hasColumn() fails due to restricted database permissions.
     */
    function safe_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT `{$column}` FROM `{$table}` LIMIT 0");
                return true;
            } catch (\Throwable $fallbackException) {
                return false;
            }
        }
    }
}

if (! function_exists('safe_has_table')) {
    /**
     * Safely check if a table exists, falling back to a SELECT LIMIT 0 query
     * if Schema::hasTable() fails.
     */
    function safe_has_table(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable $e) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT 1 FROM `{$table}` LIMIT 0");
                return true;
            } catch (\Throwable $fallbackException) {
                return false;
            }
        }
    }
}

