<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DigitalMenuController extends Controller
{
    public function index()
    {
        $setting = invoice_settings_row();
        if (! $setting) {
            return redirect()
                ->route('setting.generalsettings')
                ->with('error', 'Please configure shop settings first.');
        }

        $this->ensureMenuToken($setting);
        $setting->refresh();

        $menuUrl = $this->menuUrl($setting);
        $qrUrl = $menuUrl
            ? 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=10&data=' . rawurlencode($menuUrl)
            : null;

        return view('settings.digital-menu', [
            'setting' => $setting,
            'menuUrl' => $menuUrl,
            'qrUrl' => $qrUrl,
            'enabled' => (bool) ($setting->menu_public_enabled ?? false),
            'hasColumns' => Schema::hasColumn('settings', 'menu_public_token'),
        ]);
    }

    public function save(Request $request)
    {
        $setting = invoice_settings_row();
        if (! $setting) {
            return response()->json(['status' => false, 'message' => 'Settings not found'], 404);
        }

        if (! Schema::hasColumn('settings', 'menu_public_token')) {
            return response()->json([
                'status' => false,
                'message' => 'Run database SQL: database/sql/2026_11_05_171800_add_public_menu.sql',
            ], 422);
        }

        $this->ensureMenuToken($setting);

        if ($request->has('menu_public_enabled') && Schema::hasColumn('settings', 'menu_public_enabled')) {
            $raw = $request->input('menu_public_enabled');
            $setting->menu_public_enabled = in_array($raw, [1, '1', true, 'true', 'on', 'yes'], true) ? 1 : 0;
        }

        if ($request->boolean('regenerate_token')) {
            $setting->menu_public_token = Str::lower(Str::random(32));
        }

        $setting->save();

        $menuUrl = $this->menuUrl($setting);

        return response()->json([
            'status' => true,
            'message' => 'Digital menu settings saved.',
            'menu_url' => $menuUrl,
            'enabled' => (bool) $setting->menu_public_enabled,
            'token' => $setting->menu_public_token,
            'qr_url' => $menuUrl
                ? 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=10&data=' . rawurlencode($menuUrl)
                : null,
        ]);
    }

    protected function ensureMenuToken(Setting $setting): void
    {
        if (! Schema::hasColumn('settings', 'menu_public_token')) {
            return;
        }
        if (empty($setting->menu_public_token)) {
            $setting->menu_public_token = Str::lower(Str::random(32));
            $setting->save();
        }
    }

    protected function menuUrl(Setting $setting): ?string
    {
        $token = $setting->menu_public_token ?? null;
        if (! $token) {
            return null;
        }

        return url('/m/' . $token);
    }
}
