<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicMenuController extends Controller
{
    /**
     * Guest-facing digital menu (scan QR → this page).
     * URL: /m/{tenantId}/{token}
     */
    public function show(Request $request, int $tenant, string $token)
    {
        return $this->renderMenu($request, $token);
    }

    /**
     * Legacy /m/{token} — only works when tenant session already exists (logged-in browser).
     */
    public function legacy(Request $request, string $token)
    {
        if (! session()->has('tenant_id')) {
            abort(404, 'Menu link is outdated. Please scan the latest QR from Digital Menu settings.');
        }

        return $this->renderMenu($request, $token);
    }

    protected function renderMenu(Request $request, string $token)
    {
        $token = trim($token);
        if ($token === '' || ! Schema::hasColumn('settings', 'menu_public_token')) {
            abort(404);
        }

        $setting = Setting::where('menu_public_token', $token)->first();
        if (! $setting) {
            abort(404, 'Menu not found');
        }

        $enabled = Schema::hasColumn('settings', 'menu_public_enabled')
            ? (bool) ($setting->menu_public_enabled ?? false)
            : true;

        if (! $enabled) {
            return response()
                ->view('public.menu-disabled', ['setting' => $setting], 403);
        }

        $branchId = $setting->branch_id;

        $categories = Category::query()
            ->where('isDeleted', 0)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereHas('products', function ($q) use ($branchId) {
                $this->applyMenuProductScope($q, $branchId);
            })
            ->with(['products' => function ($q) use ($branchId) {
                $this->applyMenuProductScope($q, $branchId);
                $q->with(['variants' => function ($vq) {
                    $vq->where('isDeleted', 0)->orderBy('sort_order');
                }])->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $currency = $setting->currency_symbol ?? '₹';
        $currencyPos = $setting->currency_position ?? 'left';

        return view('public.menu', [
            'setting' => $setting,
            'categories' => $categories,
            'currency' => $currency,
            'currencyPos' => $currencyPos,
            'search' => trim((string) $request->get('q', '')),
        ]);
    }

    protected function applyMenuProductScope($query, $branchId): void
    {
        $query->where('isDeleted', 0)
            ->where(function ($status) {
                $status->where('status', 'active')->orWhereNull('status');
            })
            ->where(function ($pos) {
                $pos->where('is_pos_available', 1)->orWhereNull('is_pos_available');
            });

        if (! empty($branchId)) {
            $query->where(function ($branch) use ($branchId) {
                $branch->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
    }
}
