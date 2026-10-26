<?php

namespace App\Http\Controllers\LicenseServer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LicenseAdminAuthController extends Controller
{
    public function showLogin()
    {
        if (! config('license.admin_enabled')) {
            abort(404);
        }

        if (session('license_admin_authenticated')) {
            return redirect()->route('license-admin.dashboard');
        }

        return view('license-admin.login');
    }

    public function login(Request $request)
    {
        if (! config('license.admin_enabled')) {
            abort(404);
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $expectedEmail = strtolower((string) config('license.admin_email'));
        $expectedPassword = (string) config('license.admin_password');

        if ($expectedPassword === '') {
            return back()->with('error', 'LICENSE_ADMIN_PASSWORD is not configured on the server.');
        }

        if (
            strtolower($request->input('email')) === $expectedEmail
            && hash_equals($expectedPassword, $request->input('password'))
        ) {
            $request->session()->regenerate();
            session(['license_admin_authenticated' => true]);

            return redirect()->route('license-admin.dashboard');
        }

        return back()->withInput($request->only('email'))->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('license_admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('license-admin.login');
    }
}

