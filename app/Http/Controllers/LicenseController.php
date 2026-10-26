<?php

namespace App\Http\Controllers;

use App\Services\License\DomainNormalizer;
use App\Services\License\LicenseManager;
use App\Services\License\LicenseSigner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class LicenseController extends Controller
{
    public function __construct(
        private readonly LicenseManager $licenses,
        private readonly LicenseSigner $signer
    ) {}

    public function showActivate(Request $request)
    {
        if (! $this->licenses->isEnforcementEnabled()) {
            return redirect()->route('auth.signin');
        }

        if ($this->licenses->allowsAccess()) {
            return redirect()->intended($this->licenseHomeUrl());
        }

        return view('license.activate', [
            'status' => $this->licenses->status(),
            'mode' => config('license.mode'),
            'domain' => DomainNormalizer::fromRequest(),
            'product' => config('app.name'),
        ]);
    }

    private function licenseHomeUrl(): string
    {
        if (Auth::guard('web')->check()) {
            return route('auth.dashboard');
        }

        return route('auth.signin');
    }

    public function activate(Request $request)
    {
        if (! $this->licenses->isEnforcementEnabled()) {
            return redirect()->route('auth.signin');
        }

        $validated = $request->validate([
            'purchase_code' => ['required', 'string', 'min:10', 'max:64'],
            'buyer' => ['required', 'string', 'min:2', 'max:120'],
            'license_key' => ['nullable', 'string', 'min:20'],
        ]);

        try {
            if (config('license.mode') === 'offline') {
                if (empty($validated['license_key'])) {
                    throw new RuntimeException('License key is required in offline mode.');
                }
                $this->licenses->activateOffline($validated['license_key']);
            } else {
                $this->licenses->activateRemote(
                    $validated['purchase_code'],
                    $validated['buyer'],
                    DomainNormalizer::fromRequest()
                );
            }

            $check = $this->licenses->validate();
            if (! $check->valid) {
                throw new RuntimeException(
                    'Activation was accepted but this server could not store or verify the license. '
                    .$check->message
                );
            }

            return redirect()
                ->intended($this->licenseHomeUrl())
                ->with('success', 'License activated successfully. You can sign in now.');
        } catch (RuntimeException $e) {
            return back()
                ->withInput($request->except('license_key'))
                ->with('license_error', $e->getMessage());
        }
    }

    public function addDomain(Request $request)
    {
        $validated = $request->validate([
            'purchase_code' => ['required', 'string', 'min:10', 'max:64'],
            'buyer' => ['required', 'string', 'min:2', 'max:120'],
            'domain' => ['required', 'string', 'min:3', 'max:253'],
        ]);

        try {
            $this->licenses->addDomainRemote(
                $validated['purchase_code'],
                $validated['buyer'],
                $validated['domain']
            );

            return back()->with('success', 'Domain added to your license.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('license_error', $e->getMessage());
        }
    }

    public function status()
    {
        return response()->json($this->licenses->status());
    }

    public function deactivate(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'in:DEACTIVATE'],
        ]);

        $this->licenses->deactivate();

        return redirect()
            ->route('license.activate')
            ->with('success', 'License removed from this installation.');
    }
}
