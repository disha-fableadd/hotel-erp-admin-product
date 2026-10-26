<?php

namespace App\Http\Controllers\LicenseServer;

use App\Http\Controllers\Controller;
use App\Services\License\LicenseServerService;
use Illuminate\Http\Request;

class LicenseApiController extends Controller
{
    public function __construct(
        private readonly LicenseServerService $server
    ) {}

    public function activate(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'string'],
            'purchase_code' => ['required', 'string'],
            'buyer' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'installation_id' => ['required', 'string', 'size:64'],
        ]);

        return $this->respond($this->server->activate($data));
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'string'],
            'purchase_code_hash' => ['required', 'string', 'size:64'],
            'buyer' => ['required', 'string'],
            'domain' => ['nullable', 'string'],
            'installation_id' => ['required', 'string', 'size:64'],
        ]);

        return $this->respond($this->server->verify($data));
    }

    public function addDomain(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'string'],
            'purchase_code' => ['required', 'string'],
            'buyer' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'installation_id' => ['required', 'string', 'size:64'],
        ]);

        return $this->respond($this->server->addDomain($data));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function respond(array $result)
    {
        $status = empty($result['success'])
            ? (int) ($result['_http_status'] ?? 403)
            : 200;

        unset($result['_http_status']);

        return response()->json($result, $status);
    }
}

