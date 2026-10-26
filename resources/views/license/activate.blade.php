@php
if (! function_exists('image_path_base')) {
    function image_path_base(): string {
        $raw = env('IMAGE_PATH') ?? env('ImagePath') ?? env('APP_URL', '');
        return rtrim((string) $raw, '/').'/';
    }
}
if (! function_exists('image_path')) {
    function image_path(?string $path = ''): string {
        $base = image_path_base();
        if ($path === null || $path === '') {
            return $base;
        }
        return $base.ltrim($path, '/');
    }
}
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activate License — {{ $product }}</title>
    <link rel="stylesheet" href="{{ image_path('admin/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ image_path('admin/assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ image_path('admin/assets/css/style.css') }}">
    <style>
        .license-box { max-width: 520px; margin: 2rem auto; }
        .domain-badge { font-family: monospace; font-size: 0.9rem; }
    </style>
</head>

<body class="account-page">
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content license-box">
                    <div class="login-userset">
                        <div class="login-userheading">
                            <h3>Activate License</h3>
                            <h4>Enter your CodeCanyon / Envato purchase details</h4>
                        </div>

                        @if (session('license_error'))
                            <div class="alert alert-danger">{{ session('license_error') }}</div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($status['valid'] ?? false)
                            <div class="alert alert-success">
                                License is active for <strong>{{ $status['license_type'] ?? 'regular' }}</strong>.
                                @if (!empty($status['domains']))
                                    <br>Domains: {{ implode(', ', $status['domains']) }}
                                @endif
                            </div>
                            <p class="text-muted"><a href="{{ route('auth.signin') }}">Go to login</a></p>
                        @else
                            <p class="text-muted mb-3">
                                Detected domain: <span class="domain-badge badge bg-secondary">{{ $domain ?? 'unknown' }}</span>
                            </p>

                            <form method="POST" action="{{ route('license.activate.submit') }}">
                                @csrf
                                <div class="form-login mb-3">
                                    <label>Purchase Code</label>
                                    <input type="text" name="purchase_code" class="form-control"
                                        value="{{ old('purchase_code') }}"
                                        placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required>
                                </div>
                                <div class="form-login mb-3">
                                    <label>Envato Username</label>
                                    <input type="text" name="buyer" class="form-control"
                                        value="{{ old('buyer') }}" placeholder="Your CodeCanyon username" required>
                                </div>

                                @if ($mode === 'offline')
                                    <div class="form-login mb-3">
                                        <label>License Key</label>
                                        <textarea name="license_key" class="form-control" rows="4"
                                            placeholder="Paste the license key from the author">{{ old('license_key') }}</textarea>
                                        <small class="text-muted">Provided by the author after purchase (offline mode).</small>
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-login w-100">Activate License</button>
                            </form>

                            @if ($mode === 'remote' && ($status['license_type'] ?? '') === 'extended')
                                <hr class="my-4">
                                <h5>Add another domain (Extended license)</h5>
                                <form method="POST" action="{{ route('license.add-domain') }}">
                                    @csrf
                                    <div class="form-login mb-3">
                                        <label>Purchase Code</label>
                                        <input type="text" name="purchase_code" class="form-control" required>
                                    </div>
                                    <div class="form-login mb-3">
                                        <label>Envato Username</label>
                                        <input type="text" name="buyer" class="form-control" required>
                                    </div>
                                    <div class="form-login mb-3">
                                        <label>New Domain</label>
                                        <input type="text" name="domain" class="form-control"
                                            placeholder="client2.example.com" required>
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary w-100">Register Domain</button>
                                </form>
                            @endif
                        @endif

                        <p class="text-muted small mt-4 mb-0">
                            Regular license: one production domain. Extended: multiple domains.
                            Development: localhost only.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

