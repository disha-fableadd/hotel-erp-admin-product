@php
    use App\Models\Setting;
    $settings = Setting::first();
    $imagePath = rtrim(env('ImagePath') ?? '', '/');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Fablead Inventory-Billing Software">
    <meta name="keywords"
        content="inventory management, billing system, invoice generator, purchase orders, inventory control, POS system, admin dashboard">
    <meta name="author" content="Fablead Developers Technolab">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ $imagePath . '/admin/assets/img/favicon-192x192.webp' }}">

    {{-- Bootstrap CSS: local first, CDN fallback --}}
    <link rel="stylesheet" href="{{ asset('admin/assets/css/bootstrap.min.css') }}" id="bootstrap-css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" id="bootstrap-cdn-css" media="none">

    {{-- Font Awesome: local first, CDN always available as backup --}}
    <link rel="stylesheet" href="{{ asset('admin/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/plugins/fontawesome/css/all.min.css') }}" id="fa-local">
    {{-- FA CDN (always loaded as guaranteed fallback) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}?v={{ time() }}">

    {{-- Detect Bootstrap CSS 404 and activate CDN --}}
    <script>
        (function() {
            var testEl = document.createElement('span');
            testEl.className = 'd-none';
            testEl.style.display = 'block'; // bootstrap would override this
            document.head.appendChild(testEl);
            window.addEventListener('DOMContentLoaded', function() {
                var computed = window.getComputedStyle(testEl).display;
                if (computed !== 'none') {
                    // Bootstrap didn't load — activate CDN fallback
                    var cdn = document.getElementById('bootstrap-cdn-css');
                    if (cdn) cdn.removeAttribute('media');
                }
                document.head.removeChild(testEl);
            });
        })();
    </script>
    <style>
        :root {
            --auth-primary: #F7933A;
            --auth-primary-dark: #ea7417;
            --auth-secondary: #1F2937;
            --auth-muted: #667085;
            --auth-border: #d9dee8;
            --auth-bg: #f7f8fb;
        }

        * {
            box-sizing: border-box;
        }

        html {
            height: 100%;
        }

        body.account-page {
            position: relative;
            display: flex;
            flex-direction: column;
            margin: 0;
            /* min-height: 100vh;
            min-height: 100dvh; */
            height: 100vh;
            height: 100dvh;
            padding: clamp(10px, 2.4vh, 24px) 20px clamp(8px, 1.7vh, 16px);
            /* overflow-y: auto; */
            overflow: hidden;
            color: var(--auth-secondary);
            font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(circle at 0% 68%, rgba(247, 147, 58, .10) 0 0, transparent 168px),
                radial-gradient(circle at 100% 78%, rgba(247, 147, 58, .12) 0 0, transparent 198px),
                linear-gradient(135deg, #ffffff 0%, #f6f8fb 46%, #ffffff 100%);
        }

        body.account-page::before,
        body.account-page::after {
            content: "";
            position: fixed;
            pointer-events: none;
            z-index: 0;
            opacity: .24;
        }

        body.account-page::before {
            top: -88px;
            left: -82px;
            width: 360px;
            height: 255px;
            border-radius: 0 0 100% 0;
            background: repeating-radial-gradient(ellipse at 0% 0%, transparent 0 13px, rgba(247, 147, 58, .55) 14px 15px, transparent 16px 24px);
        }

        body.account-page::after {
            right: -92px;
            bottom: -78px;
            width: 380px;
            height: 260px;
            border-radius: 100% 0 0 0;
            background: repeating-radial-gradient(ellipse at 100% 100%, transparent 0 13px, rgba(247, 147, 58, .52) 14px 15px, transparent 16px 24px);
        }

        .main-wrapper {
            position: relative;
            z-index: 1;
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .account-content,
        .login-wrapper {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .login-wrapper .login-content {
            width: 100% !important;
            max-width: 600px !important;
            display: block !important;
        }

        .login-userset {
            position: relative;
            width: 100%;
            max-height: calc(100vh - 58px);
            max-height: calc(100dvh - 58px);
            padding: clamp(18px, 3vh, 25px) clamp(34px, 5vw, 68px);
            border: 0;
            border-radius: 22px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 2px 8px rgba(31, 41, 55, .13);
            backdrop-filter: blur(16px);
        }

        .login-userset::before {
            content: "";
            position: absolute;
            top: -1px;
            left: 50%;
            width: 92px;
            height: 4px;
            border-radius: 0 0 999px 999px;
            transform: translateX(-50%);
            background: var(--auth-primary);
        }

        .login-logo {
            display: flex;
            justify-content: center;
            margin: 0 0 clamp(10px, 1.9vh, 18px);
            text-align: center;
        }

        .login-logo .logo-img {
            display: block;
            width: 190px;
            max-width: 70%;
            max-height: 90px;
            object-fit: contain;
            margin: 0 auto !important;
        }

        .login-userheading {
            margin-bottom: clamp(16px, 2.8vh, 24px);
            text-align: center;
        }

        .login-userheading h3 {
            margin: 0 0 clamp(5px, 1vh, 8px);
            color: var(--auth-secondary);
            font-size: clamp(24px, 3.8vh, 30px);
            line-height: 1.18;
            font-weight: 800;
            letter-spacing: 0;
        }

        .login-userheading h3 span {
            color: var(--auth-primary);
        }

        .login-userheading h4 {
            margin: 0;
            color: var(--auth-muted);
            font-size: clamp(13px, 1.9vh, 15px);
            line-height: 1.55;
            font-weight: 400;
        }

        .login-wrapper .login-content .form-login {
            margin-bottom: clamp(12px, 2vh, 17px) !important;
        }

        .login-wrapper .login-content .form-login label {
            display: block !important;
            margin-bottom: clamp(5px, 1vh, 8px) !important;
            color: #111827 !important;
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .login-wrapper .login-content .form-login .form-addons,
        .login-wrapper .login-content .form-login .pass-group {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            min-height: clamp(42px, 6.3vh, 48px) !important;
            border: 1px solid var(--auth-border) !important;
            border-radius: 9px !important;
            background: #fff !important;
            overflow: hidden !important;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease !important;
        }

        .login-wrapper .login-content .form-login .form-addons:focus-within,
        .login-wrapper .login-content .form-login .pass-group:focus-within {
            border-color: rgba(247, 147, 58, .78) !important;
            box-shadow: 0 0 0 4px rgba(247, 147, 58, .14) !important;
        }

        .login-wrapper .login-content .form-login .form-addons::before,
        .login-wrapper .login-content .form-login .pass-group::before {
            content: "" !important;
            align-self: stretch !important;
            width: 56px !important;
            flex: 0 0 56px !important;
            border-right: 0 !important;
            background: #fff !important;
        }

        .login-wrapper .login-content .form-login .form-addons::after,
        .login-wrapper .login-content .form-login .pass-group::after {
            position: absolute !important;
            left: 19px !important;
            z-index: 2 !important;
            color: var(--auth-primary) !important;
            font-family: "Font Awesome 5 Free" !important;
            font-size: 16px !important;
            font-weight: 900 !important;
            line-height: 1 !important;
        }

        .login-wrapper .login-content .form-login .form-addons::after {
            content: "\f0e0" !important;
        }

        .login-wrapper .login-content .form-login .pass-group::after {
            content: "\f023" !important;
        }

        /* Complete reset for input fields inside forms to strip borders, outlines, shadows, and backgrounds */
        .login-wrapper .login-content .form-login input,
        .login-wrapper .login-content .form-login input:focus,
        .login-wrapper .login-content .form-login .form-addons input,
        .login-wrapper .login-content .form-login .form-addons input:focus,
        .login-wrapper .login-content .form-login .pass-group input,
        .login-wrapper .login-content .form-login .pass-group input:focus {
            width: 100% !important;
            height: clamp(42px, 6.3vh, 48px) !important;
            min-width: 0 !important;
            padding: 0 18px 0 8px !important;
            border: 0 none !important;
            border-style: none !important;
            outline: 0 !important;
            color: var(--auth-secondary) !important;
            font-size: 15px !important;
            background: transparent !important;
            background-color: transparent !important;
            margin-right: 0 !important;
            box-shadow: none !important;
        }

        /* Prevent and override browser autofill backgrounds and borders */
        .login-wrapper .login-content .form-login input:-webkit-autofill,
        .login-wrapper .login-content .form-login input:-webkit-autofill:hover,
        .login-wrapper .login-content .form-login input:-webkit-autofill:focus,
        .login-wrapper .login-content .form-login input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            -webkit-text-fill-color: var(--auth-secondary) !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }

        .login-wrapper .login-content .form-login .form-addons input::placeholder,
        .login-wrapper .login-content .form-login .pass-group input::placeholder {
            color: #7a8597 !important;
        }

        .login-wrapper .login-content .form-login .form-addons img {
            display: none !important;
        }

        .login-wrapper .login-content .form-login .pass-group .toggle-password {
            position: static !important;
            transform: none !important;
            width: auto !important;
            height: auto !important;
            flex: 0 0 48px !important;
            color: #8a94a6 !important;
            cursor: pointer !important;
            text-align: center !important;
            transition: color .2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .login-wrapper .login-content .form-login .pass-group .toggle-password:hover {
            color: var(--auth-primary) !important;
        }

        .signin-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-top: -1px;
            margin-bottom: clamp(14px, 2.5vh, 21px);
        }

        .remember-login {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            color: var(--auth-secondary);
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            cursor: pointer;
        }

        .remember-login input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--auth-primary);
            cursor: pointer;
        }

        .forgot-link {
            color: var(--auth-primary-dark);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: color .2s ease;
        }

        .forgot-link:hover,
        .forgot-link:focus {
            color: var(--auth-primary);
            text-decoration: none;
        }

        .login-wrapper .login-content .form-login .btn-login {
            width: 100% !important;
            min-height: clamp(42px, 6.5vh, 48px) !important;
            border: 0 !important;
            border-radius: 30px !important;
            color: #fff !important;
            background: linear-gradient(135deg, #ff9b45 0%, var(--auth-primary) 54%, #f47d23 100%) !important;
            box-shadow: 0 13px 26px rgba(247, 147, 58, .30) !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            letter-spacing: 0 !important;
            padding: 10px !important;
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease !important;
        }

        .login-wrapper .login-content .form-login .btn-login:hover,
        .login-wrapper .login-content .form-login .btn-login:focus {
            color: #fff !important;
            transform: translateY(-1px) !important;
            filter: saturate(1.04) !important;
            box-shadow: 0 16px 30px rgba(247, 147, 58, .36) !important;
            background: linear-gradient(135deg, #ff9b45 0%, var(--auth-primary) 54%, #f47d23 100%) !important;
        }

        .login-wrapper .login-content .form-login .btn-login:active {
            transform: translateY(0) !important;
        }

        .signin-arrow {
            margin-left: 12px;
            font-weight: 500;
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 24px;
            margin: clamp(15px, 2.5vh, 22px) 0 clamp(10px, 1.8vh, 14px);
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .login-divider::before,
        .login-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #e2e7ef;
        }

        #faceLoginBtn {
            width: 100% !important;
            min-height: clamp(42px, 6.3vh, 48px);
            border: 1px solid #e2e7ef !important;
            border-radius: 999px !important;
            background: #fff !important;
            color: var(--auth-secondary) !important;
            box-shadow: 0 8px 22px rgba(31, 41, 55, .07);
            font-size: 14px !important;
            font-weight: 700 !important;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
        }

        #faceLoginBtn i {
            color: var(--auth-primary);
            font-size: 15px !important;
        }

        #faceLoginBtn:hover,
        #faceLoginBtn:focus {
            border-color: rgba(247, 147, 58, .55) !important;
            color: var(--auth-primary-dark) !important;
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(31, 41, 55, .10);
        }

        #loginMessage,
        #faceLoginMessage {
            text-align: center;
            font-size: 13px;
            font-weight: 500;
        }

        .login-footer {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            padding-top: clamp(6px, 1.4vh, 12px);
            text-align: center;
            color: #667085;
        }

        .login-footer h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .login-footer a {
            color: inherit;
            text-decoration: none;
        }

        .login-footer h1:not(.login-footer-title) {
            display: none;
        }

        @media screen and (max-width: 768px) {
            body.account-page {
                padding: 12px 14px 10px;
            }

            .main-wrapper {
                min-height: 0;
            }

            .login-wrapper .login-content {
                width: 100% !important;
            }

            .login-userset {
                padding: 18px 22px;
                border-radius: 20px;
            }

            .login-logo .logo-img {
                width: 112px;
                max-width: 72%;
            }

            .login-userheading h3 {
                font-size: 28px;
            }

            .signin-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }
        }

        @media screen and (max-width: 420px) {
            .login-userset {
                padding: 16px 16px;
            }

            .login-userheading h3 {
                font-size: 26px;
            }

            .login-userheading h4 {
                font-size: 14px;
            }
        }

        @media screen and (max-height: 680px) {
            body.account-page {
                padding-top: 8px;
                padding-bottom: 6px;
            }

            .login-userset {
                padding-top: 16px;
                padding-bottom: 16px;
                border-radius: 18px;
            }

            .login-logo {
                margin-bottom: 8px;
            }

            .login-logo .logo-img {
                width: 98px;
                max-height: 58px;
            }

            .login-userheading {
                margin-bottom: 12px;
            }

            .login-userheading h3 {
                font-size: 23px;
                margin-bottom: 4px;
            }

            .login-userheading h4 {
                font-size: 12px;
            }

            .form-login {
                margin-bottom: 10px;
            }

            .form-addons,
            .pass-group {
                min-height: 40px;
            }

            .form-addons input,
            .pass-group input {
                height: 40px;
            }

            .signin-options {
                margin-bottom: 12px;
            }

            .btn-login,
            #faceLoginBtn {
                min-height: 40px;
            }

            .login-divider {
                margin: 12px 0 8px;
            }

            .login-footer h1 {
                font-size: 12px;
            }
        }
    </style>
</head>

<body class="account-page">

    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <div class="login-userset">
                        <!-- <div class="login-logo">
                            @if($settings?->logo)
                                <img src="{{ !empty($settings) && !empty($settings->logo) ? env('ImagePath').'/storage/' . $settings->logo : 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp' }}"
                                    alt="Shop Logo" class="logo-img"
                                    style="width:auto; max-width:200px; height:auto; max-height:80px; object-fit:contain; margin:0 auto; display:block;"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div style="display:none; justify-content:center; align-items:center; width:82px; height:82px; border-radius:50%; background:linear-gradient(145deg,#dde6f5 0%,#c5d5ee 100%); border:3px solid #e2e8f0; margin:0 auto;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="50" height="50" fill="none">
                                        <circle cx="32" cy="22" r="13" fill="#B8C9EE"/>
                                        <circle cx="32" cy="22" r="11" fill="#ccdaf5"/>
                                        <ellipse cx="32" cy="52" rx="20" ry="13" fill="#B8C9EE"/>
                                        <ellipse cx="32" cy="51" rx="18" ry="11" fill="#ccdaf5"/>
                                        <polygon points="29,37 35,37 37,53 32,57 27,53" fill="#F7933A"/>
                                        <polygon points="29,37 35,37 32,42" fill="#c96010"/>
                                    </svg>
                                </div>
                            @else
                                <div style="display:flex; justify-content:center; align-items:center; width:82px; height:82px; border-radius:50%; background:linear-gradient(145deg,#dde6f5 0%,#c5d5ee 100%); border:3px solid #e2e8f0; margin:0 auto;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="50" height="50" fill="none">
                                        <circle cx="32" cy="22" r="13" fill="#B8C9EE"/>
                                        <circle cx="32" cy="22" r="11" fill="#ccdaf5"/>
                                        <ellipse cx="32" cy="52" rx="20" ry="13" fill="#B8C9EE"/>
                                        <ellipse cx="32" cy="51" rx="18" ry="11" fill="#ccdaf5"/>
                                        <polygon points="29,37 35,37 37,53 32,57 27,53" fill="#F7933A"/>
                                        <polygon points="29,37 35,37 32,42" fill="#c96010"/>
                                    </svg>
                                </div>
                            @endif
                        </div> -->
                        <div class="login-logo">
                            <img src="{{ $imagePath . '/admin/assets/img/Fablead_ERP_2048x1024.webp' }}"
                                alt="Fablead ERP Logo" class="logo-img">
                        </div>
                        <div class="login-userheading">
                            <h3>Welcome <span>Back</span></h3>
                            <h4>Please sign in to your account to continue</h4>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success mb-3">{{ session('success') }}</div>
                        @endif

                        <form id="loginForm">
                            <div class="form-login">
                                <label>Username</label>
                                <div class="form-addons">
                                    <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username">
                                    <img src="{{ $imagePath . '/admin/assets/img/icons/users1.svg' }}" alt="img" onerror="this.src='{{ $imagePath . '/admin/assets/img/icons/mail.svg' }}'">
                                </div>
                                <small id="usernameError" class="text-danger"></small>
                            </div>

                            <div class="form-login">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" id="password" class="pass-input"
                                        placeholder="Enter your password">
                                    <span class="fas toggle-password fa-eye-slash" id="togglePasswordBtn" style="cursor:pointer;"></span>
                                </div>
                                <small id="passwordError" class="text-danger"></small>
                            </div>

                            <div class="signin-options">
                                <label class="remember-login">
                                    <input type="checkbox" id="remember">
                                    <span>Remember me</span>
                                </label>
                            </div>

                            <div class="form-login">
                                <button type="submit" class="btn btn-login">Sign In <i class="fa fa-arrow-right-to-bracket"></i></button>
                            </div>

                            <div id="loginMessage" class="text-danger"></div>

                            {{-- ── Face Login ─────────────────────────────────────── --}}
                            <!-- <div class="form-login mt-2">
                                <div class="login-divider">
                                    <span>or continue with</span>
                                </div>
                                <button type="button" id="faceLoginBtn"
                                    style="width:100%;padding:13px 20px;border-radius:10px;border:2px solid #ff9f43;
                                           background:#fff;color:#f97316;font-weight:700;font-size:14px;
                                           cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
                                           transition:all .2s ease;">
                                    <i class="fas fa-camera" style="font-size:15px;"></i> Login with Face
                                </button>
                                <div id="faceLoginMessage" class="text-danger mt-2" style="font-size:13px;"></div>
                            </div> -->
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="login-footer">
        <h1 class="login-footer-title">&copy; <?= date('Y') ?> <a href="https://www.fableadtech.com/" target="_blank">Copyright - Fablead Developers Technolab</a></h1>
        <h1 style="font-size: 14px; font-weight: 600; margin: 0;">© <?= date('Y') ?> <a href="https://fableadtechnolabs.com" target="_blank" style="color: inherit; text-decoration: none;">Copyright - Fablead Developers Technolab</a></h1>
    </footer>

    <!-- face-api.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    {{-- jQuery: local first, CDN fallback if local returns 404 --}}
    <script src="{{ asset('admin/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>')</script>

    <script src="{{ asset('admin/assets/js/feather.min.js') }}"></script>

    {{-- Bootstrap JS: local first, CDN fallback --}}
    <script src="{{ asset('admin/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script>if (typeof bootstrap === 'undefined') { document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>') }</script>

    <script src="{{ asset('admin/assets/js/script.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ── Face Recognition Modal & Script Partials ─────── --}}
    @include('partials.face-recognition-modal')
    @include('partials.face-recognition-script')

    <script>
        $(document).ready(function () {
            // ── Password Show / Hide Toggle ────────────────────────────
            // $('#togglePasswordBtn').on('click', function () {
            //     var $input = $('#password');
            //     var isHidden = $input.attr('type') === 'password';
            //     $input.attr('type', isHidden ? 'text' : 'password');
            //     $(this).toggleClass('fa-eye-slash fa-eye');
            // });
            $('#togglePasswordBtn').on('click', function (e) {
                e.stopImmediatePropagation();

                let input = document.getElementById("password");

                if (input.type === "password") {
                    input.type = "text";
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.type = "password";
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });
            function getCurrentLocation() {
                return new Promise(function (resolve) {
                    if (!navigator.geolocation) {
                        resolve({ latitude: null, longitude: null, location_name: null });
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(function (position) {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            location_name: null,
                        });
                    }, function () {
                        resolve({ latitude: null, longitude: null, location_name: null });
                    }, {
                        enableHighAccuracy: true,
                        timeout: 7000,
                        maximumAge: 0
                    });
                });
            }

            /* ── Normal username/password login ─────────────────────────── */
            $("#loginForm").submit(function (e) {
                e.preventDefault();

                let username = ($("#username").val() || $("#email").val() || "").trim();
                let password = $("#password").val().trim();
                $("#usernameError, #emailError, #passwordError, #loginMessage").text("");

                if (!username) {
                    $("#usernameError, #emailError").text("Username is required.");
                    return;
                }
                if (!password) {
                    $("#passwordError").text("Password is required.");
                    return;
                }

                let $submitBtn = $("#loginForm button[type='submit']");
                let originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Logging in...').prop("disabled", true);

                async function performLogin(forceLogin = false) {
                    const location = await getCurrentLocation();

                    $.ajax({
                        url: "{{ url('/') }}",
                        type: "POST",
                        dataType: "json",
                        data: {
                            username: username,
                            email: username,
                            password: password,
                            force: forceLogin ? 1 : 0,
                            latitude: location.latitude,
                            longitude: location.longitude,
                            location_name: location.location_name,
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function (response) {
                            console.log("Response:", response);

                            // ⚠️ Show warning popup if already checked in/out
                            if (response.warning && response.status === true && !forceLogin) {
                                Swal.fire({
                                    title: "Warning",
                                    text: response.message,
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, log me in",
                                    cancelButtonText: "Cancel"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        performLogin(true);
                                    }
                                });
                                return;
                            }

                            // ✅ Successful login
                            if (response.status && response.token) {
                                localStorage.setItem("authToken", response.token);
                                localStorage.setItem("token", response.token);

                                if (response.user && response.user.role === "admin") {
                                    localStorage.removeItem("selectedSubAdminId");
                                    window.location.href = response.redirect;
                                } else if (response.user) {
                                    const selectedId = response.user.branch_id ?? response.user.id;
                                    localStorage.setItem("selectedSubAdminId", selectedId);

                                    // Set sub-branch session
                                    $.post('/set-subadmin-session', {
                                        _token: $('meta[name="csrf-token"]').attr('content'),
                                        subAdminId: selectedId
                                    }, function() {
                                        window.location.href = response.redirect;
                                    });
                                } else {
                                    window.location.href = response.redirect;
                                }
                            } else if (response.error) {
                                $("#loginMessage").text(response.error).css("color", "red");
                            }
                        },
                        error: function (xhr) {
                            console.error("Error:", xhr.responseText);
                            let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "Login failed.";
                            if (xhr.responseJSON?.license_required) {
                                msg += ' Go to <a href="{{ url('/license') }}">/license</a> to activate.';
                            }
                            $("#loginMessage").html(msg).css("color", "red");
                        },
                        complete: function () {
                            $submitBtn.html(originalText).prop("disabled", false);
                        }
                    });
                }

                performLogin(false);
            });

            /* ── Face Login ──────────────────────────────────────────── */
            const $faceBtn = $('#faceLoginBtn');
            const $faceMsgBox = $('#faceLoginMessage');

            $faceBtn.on('click', function () {
                $faceMsgBox.text('');
                let fr;
                try {
                    fr = window.OmsaiFaceRecognition.init();
                } catch (err) {
                    $faceMsgBox.text('Face recognition UI failed to initialise. Please refresh.');
                    return;
                }

                fr.open({
                    title:        'Login with Face',
                    subtitle:     'Look straight at the camera. We will log you in automatically.',
                    autoDetect:   true,
                    onCapture: async function (descriptor, modal) {
                        modal.setStatus('⏳ Verifying face...', 'info');

                        try {
                            const location = await getCurrentLocation();
                            const response = await $.ajax({
                                url:  '{{ route("auth.face-login") }}',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    face_descriptor: descriptor,
                                    latitude: location.latitude,
                                    longitude: location.longitude,
                                    location_name: location.location_name,
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                }),
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            });

                            if (response.status && response.redirect) {
                                modal.setStatus('✅ Face matched! Redirecting...', 'success');

                                // Store auth data same as password login
                                if (response.token) {
                                    localStorage.setItem('authToken', response.token);
                                    localStorage.setItem('token', response.token);
                                }
                                if (response.user) {
                                    const role = (response.user.role || '').toLowerCase();
                                    if (role === 'admin' || role === 'staff' || role === 'hr') {
                                        if (role === 'admin') {
                                            localStorage.removeItem('selectedSubAdminId');
                                        } else {
                                            const selectedId = response.user.branch_id ?? response.user.id;
                                            localStorage.setItem('selectedSubAdminId', selectedId);
                                        }
                                        setTimeout(() => {
                                            modal.close();
                                            window.location.href = response.redirect;
                                        }, 800);
                                    } else {
                                        const selectedId = response.user.branch_id ?? response.user.id;
                                        localStorage.setItem('selectedSubAdminId', selectedId);

                                        // Set sub-branch session
                                        $.post('/set-subadmin-session', {
                                            _token: $('meta[name="csrf-token"]').attr('content'),
                                            subAdminId: selectedId
                                        }, function() {
                                            setTimeout(() => {
                                                modal.close();
                                                window.location.href = response.redirect;
                                            }, 800);
                                        }).fail(function() {
                                            // Fallback redirect if post fails (e.g. CSRF mismatch)
                                            setTimeout(() => {
                                                modal.close();
                                                window.location.href = response.redirect;
                                            }, 800);
                                        });
                                    }
                                } else {
                                    setTimeout(() => {
                                        modal.close();
                                        window.location.href = response.redirect;
                                    }, 800);
                                }
                            } else {
                                throw new Error(response.error || 'Face login failed.');
                            }
                        } catch (err) {
                            const msg = err.responseJSON?.error || err.responseJSON?.message || err.message || 'Face login failed.';
                            throw new Error(msg);
                        }
                    },
                });
            });

            // Hover effects for face login button
            $faceBtn.on('mouseenter', function () {
                $(this).css({ 'background': 'linear-gradient(135deg,#ff9f43,#f97316)', 'color': '#fff' });
            }).on('mouseleave', function () {
                $(this).css({ 'background': '#fff', 'color': '#f97316' });
            });

        });
    </script>
</body>

</html>
