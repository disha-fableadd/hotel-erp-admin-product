<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceCustomizeController extends Controller
{
    public function index()
    {
        $setting = invoice_settings_row();
        if (! $setting) {
            return redirect()
                ->route('setting.generalsettings')
                ->with('error', 'Please configure shop settings first.');
        }

        $inv = invoice_custom($setting);

        return view('settings.customize-invoice', [
            'setting' => $setting,
            'inv' => $inv,
        ]);
    }

    public function save(Request $request)
    {
        $setting = invoice_settings_row();
        if (! $setting) {
            return response()->json(['status' => false, 'message' => 'Settings not found'], 404);
        }

        $defaults = default_invoice_customization();
        $current = invoice_custom($setting);
        $incoming = $request->input('invoice_customization', []);
        if (! is_array($incoming)) {
            $incoming = [];
        }

        $cleaned = [];
        foreach ($defaults as $key => $defaultVal) {
            if (! array_key_exists($key, $incoming)) {
                $cleaned[$key] = $current[$key] ?? $defaultVal;
                continue;
            }
            $val = $incoming[$key];
            if (is_bool($defaultVal)) {
                $cleaned[$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            } elseif (is_int($defaultVal)) {
                $cleaned[$key] = (int) $val;
            } elseif (is_float($defaultVal)) {
                $cleaned[$key] = (float) $val;
            } else {
                $cleaned[$key] = is_string($val) ? trim($val) : $val;
            }
        }

        $cleaned['preferred_template'] = max(1, min(5, (int) ($cleaned['preferred_template'] ?? 1)));
        $cleaned['font_size'] = max(6, min(14, (int) ($cleaned['font_size'] ?? 8)));
        $cleaned['decimal_places'] = max(0, min(4, (int) ($cleaned['decimal_places'] ?? 2)));
        $cleaned['print_copies'] = max(1, min(5, (int) ($cleaned['print_copies'] ?? 1)));
        $cleaned['left_margin_mm'] = max(0, min(10, (float) ($cleaned['left_margin_mm'] ?? 0)));
        $cleaned['logo_size'] = max(0.5, min(2, (float) ($cleaned['logo_size'] ?? 1)));
        if (! in_array($cleaned['paper_size'], ['2inch', '3inch'], true)) {
            $cleaned['paper_size'] = '3inch';
        }
        if (! in_array($cleaned['language'], ['english', 'hindi'], true)) {
            $cleaned['language'] = 'english';
        }

        // Mark that shop phone was set from the Customize Invoice UI (not legacy default).
        $cleaned['shop_phone_user_set'] = true;
        $cleaned['show_shop_phone'] = ! empty($cleaned['show_shop_phone']);

        $thanksMsg = trim((string) ($cleaned['thanks_message'] ?? ''));
        if ($thanksMsg === '') {
            $thanksMsg = 'Thanks! Visit again';
            $cleaned['thanks_message'] = $thanksMsg;
        }
        // Keep legacy footer_text in sync for older invoice views
        if (\Illuminate\Support\Facades\Schema::hasColumn($setting->getTable(), 'footer_text')) {
            $setting->footer_text = $thanksMsg;
        }

        $setting->invoice_customization = $cleaned;
        $setting->save();

        return response()->json([
            'status' => true,
            'message' => 'Invoice customization saved',
            'data' => $cleaned,
        ]);
    }
}
