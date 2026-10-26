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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'License Admin') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ image_path('admin/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ image_path('admin/assets/plugins/fontawesome/css/all.min.css') }}">
    <style>
        body { background: #f4f6f9; }
        .admin-nav { background: #1e293b; }
        .admin-nav a { color: #e2e8f0; text-decoration: none; margin-right: 1rem; }
        .admin-nav a:hover { color: #fff; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .badge-active { background: #198754; }
        .badge-revoked { background: #dc3545; }
        .badge-suspended { background: #fd7e14; }
    </style>
    @stack('css')
</head>

<body>
    @if (session('license_admin_authenticated'))
        <nav class="admin-nav py-3 mb-4">
            <div class="container d-flex justify-content-between align-items-center">
                <div>
                    <strong class="text-white me-3">License Server</strong>
                    <a href="{{ route('license-admin.dashboard') }}">All codes</a>
                    <a href="{{ route('license-admin.create') }}">+ Register code</a>
                </div>
                <form method="POST" action="{{ route('license-admin.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </nav>
    @endif

    <main class="container pb-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <script src="{{ image_path('admin/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ image_path('admin/assets/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>

</html>

