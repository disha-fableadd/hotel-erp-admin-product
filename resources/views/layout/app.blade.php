@php
    $settings = App\Models\Setting::first();
    $imagePath = rtrim(env('ImagePath') ?? '', '/');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0"> -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Fablead Inventory-Billing Software">
    <meta name="keywords" content="inventory management, billing system, invoice generator, purchase orders, inventory control, POS system, admin dashboard">
    <meta name="author" content="Fablead Developers Technolab">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Default Title')</title>

    <!-- <link rel="shortcut icon" type="image/x-icon"
        href="{{ optional($settings)->favicon ? rtrim(image_path_base(), '/').'/storage/' . $settings->favicon : 'https://fableadtechnolabs.com/favicon-192x192.webp' }}"> -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('admin/assets/img/favicon-192x192.webp') }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/css/bootstrap.min.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/plugins/owlcarousel/owl.carousel.min.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/plugins/owlcarousel/owl.theme.default.min.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/css/animate.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/plugins/select2/css/select2.min.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/css/bootstrap-datetimepicker.min.css' }}">

    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/css/dataTables.bootstrap4.min.css' }}">

    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/plugins/fontawesome/css/fontawesome.min.css' }}">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/plugins/fontawesome/css/all.min.css' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ rtrim(image_path_base(), '/').'/admin/assets/css/style.css' }}">
    @stack('css')
    <style>
        .btn.ai-bill-upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 15px;
            border: 1px solid #ff9f43;
            border-radius: 5px;
            background: #ff9f43;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            white-space: nowrap;
            transition: background-color .2s, border-color .2s;
        }
        .btn.ai-bill-upload-btn:hover,
        .btn.ai-bill-upload-btn:focus-visible {
            background: #1b2850;
            border-color: #1b2850;
            color: #fff;
        }
        .btn.ai-bill-upload-btn:focus-visible {
            outline: 2px solid #ff9f43;
            outline-offset: 3px;
        }
        .purchase-page-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .purchase-page-actions .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 38px; padding: 8px 15px; border-radius: 5px; line-height: 20px;
        }
        .purchase-page-actions .btn + .btn { margin-left: 0; }
        .purchase-entry-header { flex-wrap: wrap; gap: 16px; }
        .purchase-entry-header .gst-header { flex-wrap: wrap; gap: 16px; }
        @media (max-width: 575px) {
            .purchase-page-actions { width: 100%; }
            .purchase-page-actions .btn { flex: 1; }
        }
    </style>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">

    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper" style="flex: 1;">
        @include('layout.header')
        @include('layout.sidebar')

        <!-- Main Content Section -->
        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Footer with copyright -->
    <footer style="text-align: center; padding: 10px 0; background-color: #f4f4f4; height: 50px;">
        <a href="https://www.fableadtech.com/" target="_blank">
            <h1 style="font-size: 14px; color: #333; font-weight: 600;">© <?= date('Y') ?> Copyright - Fablead Developers Technolab</h1>
        </a>
    </footer>

    @include('layout.footer')
    @stack('js')
</body>

</html>
