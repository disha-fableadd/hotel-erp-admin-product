<?php

namespace App\Http\Controllers\LicenseServer;

use App\Http\Controllers\Controller;
use App\Models\ProductLicense;
use App\Models\ProductLicenseDomain;
use App\Services\License\LicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class LicenseAdminController extends Controller
{
    public function __construct(
        private readonly LicenseManager $licenses
    ) {}

    public function index(Request $request)
    {
        $query = ProductLicense::with('domains')->orderByDesc('id');

        if ($search = trim((string) $request->input('q'))) {
            $hash = hash('sha256', strtoupper($search));
            $query->where(function ($q) use ($search, $hash) {
                $q->where('purchase_code', 'like', '%'.$search.'%')
                    ->orWhere('buyer', 'like', '%'.$search.'%')
                    ->orWhere('purchase_code_hash', $hash);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $licenses = $query->paginate(20)->withQueryString();

        return view('license-admin.index', [
            'licenses' => $licenses,
            'stats' => [
                'total' => ProductLicense::count(),
                'active' => ProductLicense::where('status', 'active')->count(),
                'activations' => ProductLicenseDomain::count(),
            ],
        ]);
    }

    public function create()
    {
        return view('license-admin.create', [
            'types' => $this->licenseTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_code' => ['required', 'string', 'min:4', 'max:64'],
            'buyer' => ['required', 'string', 'min:2', 'max:120'],
            'license_type' => ['required', Rule::in($this->licenseTypes())],
            'max_domains' => ['nullable', 'integer', 'min:1', 'max:50'],
            'envato_item_id' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'revoked', 'suspended'])],
        ]);

        $code = strtoupper(trim($validated['purchase_code']));
        $type = $validated['license_type'];
        $maxDomains = $validated['max_domains']
            ?? (int) config('license.domain_limits.'.$type, 1);

        $license = ProductLicense::updateOrCreate(
            ['purchase_code' => $code],
            [
                'purchase_code_hash' => hash('sha256', $code),
                'buyer' => trim($validated['buyer']),
                'license_type' => $type,
                'max_domains' => $maxDomains,
                'status' => $validated['status'],
                'envato_item_id' => $validated['envato_item_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $this->applyStatusSideEffects($license->fresh());

        return redirect()
            ->route('license-admin.dashboard')
            ->with('success', "Purchase code {$code} saved and {$validated['status']}.");
    }

    public function show(ProductLicense $license)
    {
        $license->load('domains');

        return view('license-admin.show', [
            'license' => $license,
            'types' => $this->licenseTypes(),
        ]);
    }

    public function update(Request $request, ProductLicense $license)
    {
        $validated = $request->validate([
            'buyer' => ['required', 'string', 'min:2', 'max:120'],
            'license_type' => ['required', Rule::in($this->licenseTypes())],
            'max_domains' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in(['active', 'revoked', 'suspended'])],
            'envato_item_id' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $license->update($validated);
        $this->applyStatusSideEffects($license->fresh());

        return redirect()
            ->route('license-admin.show', $license)
            ->with('success', 'License updated.');
    }

    public function updateStatus(Request $request, ProductLicense $license)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'revoked', 'suspended'])],
        ]);

        $license->update(['status' => $validated['status']]);
        $this->applyStatusSideEffects($license->fresh());

        return back()->with('success', 'Status set to '.$validated['status'].'. All domains using this purchase code are affected.');
    }

    private function applyStatusSideEffects(ProductLicense $license): void
    {
        Cache::put(
            'license.server_status.'.$license->purchase_code_hash,
            $license->status,
            now()->addDays(30)
        );

        if ($license->status !== 'active') {
            $this->licenses->purgeLocalLicenseIfMatches($license->purchase_code_hash);
        } else {
            Cache::forget('license.server_status.'.$license->purchase_code_hash);
        }
    }

    public function destroyDomain(ProductLicense $license, ProductLicenseDomain $domain)
    {
        if ($domain->product_license_id !== $license->id) {
            abort(404);
        }

        $removedDomain = $domain->domain;
        $domain->delete();

        Cache::put(
            'license.domains_changed.'.$license->purchase_code_hash,
            time(),
            now()->addDays(30)
        );

        $this->licenses->purgeLocalLicenseForRemovedDomain($license->purchase_code_hash, $removedDomain);

        return back()->with('success', "Domain {$removedDomain} removed. That site loses access on the next page load.");
    }

    /**
     * @return list<string>
     */
    private function licenseTypes(): array
    {
        return ['regular', 'extended', 'development'];
    }
}

