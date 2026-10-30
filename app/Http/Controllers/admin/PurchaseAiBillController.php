<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AiPurchaseBill;
use App\Models\Product;
use App\Models\User;
use App\Services\Purchase\PurchaseBillVisionService;
use App\Services\Purchase\ProductMatcher;
use App\Services\Purchase\SaveAiPurchaseBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseAiBillController extends Controller
{
    public function index(): View
    {
        $branchId = $this->branchId();
        $aiReady = app(PurchaseBillVisionService::class)->isConfigured();

        return view('purchase.ai-bill', [
            'vendors' => $this->vendors($branchId),
            'categories' => $this->categories($branchId),
            'products' => $this->products($branchId),
            'aiReady' => $aiReady,
            'recentBills' => AiPurchaseBill::where('branch_id', $branchId)->whereNotNull('purchase_invoice_id')->latest()->limit(20)->get(),
        ]);
    }

    public function parse(Request $request, PurchaseBillVisionService $vision): JsonResponse
    {
        // AI requests can outlast PHP's web execution limit. The service has
        // its own shorter network deadline so failures remain JSON responses.
        set_time_limit(120);

        $request->validate([
            'bill' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        try {
            $extracted = $vision->extract($request->file('bill'));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $branchId = $this->branchId();
        $products = $this->products($branchId);
        $vendors = $this->vendors($branchId);

        $matchedVendor = $this->matchVendor($extracted['vendor_name'], $extracted['vendor_phone'], $vendors);

        $items = [];
        foreach ($extracted['items'] as $item) {
            $quantity = $item['quantity'];
            $amount = (float) $item['amount'];

            $unitPrice = $quantity > 0 ? round($amount / $quantity, 4) : (float) $item['rate'];
            $match = app(ProductMatcher::class)->match($item['name'], $item['unit'], $products);

            $items[] = array_merge($item, [
                'name' => $item['name'],
                'hsn' => $item['hsn'],
                'bags' => $item['bags'],
                'weight' => $item['weight'],
                'rate' => $item['rate'],
                'amount' => $amount,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'product_id' => $match['id'] ?? null,
                'category_id' => $match['category_id'] ?? null,
                'current_stock' => $match['quantity'] ?? 0,
                'action' => $match ? 'update' : 'create',
            ]);
        }

        $grandTotal = (float) $extracted['grand_total'];
        $itemsTotal = round(array_sum(array_column($items, 'amount')), 2);

        $token = (string) Str::uuid();
        $path = $request->file('bill')->store('ai-bills/'.$branchId, 'local');
        try {
            AiPurchaseBill::create([
                'branch_id' => $branchId, 'created_by' => Auth::id(),
                'draft_token' => $token, 'file_path' => $path,
                'original_name' => $request->file('bill')->getClientOriginalName(),
                'extraction' => $extracted,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return response()->json([
            'success' => true,
            'draft_token' => $token,
            'warnings' => $extracted['warnings'],
            'header' => array_merge(collect($extracted)->except('items', 'warnings', 'source_payload')->all(), [
                'vendor_id' => $matchedVendor['id'] ?? null,
                'vendor_name' => $extracted['vendor_name'],
                'vendor_phone' => $extracted['vendor_phone'],
                'vendor_address' => $extracted['vendor_address'],
                'bill_no' => $extracted['bill_no'],
                'bill_date' => $extracted['bill_date'],
                'customer_name' => $extracted['customer_name'],
                'grand_total' => $grandTotal,
                'items_total' => $itemsTotal,
                'amount_in_words' => $extracted['amount_in_words'],
            ]),
            'items' => $items,
        ]);
    }

    public function save(Request $request, SaveAiPurchaseBill $save): JsonResponse
    {
        $data = $request->validate([
            'draft_token' => 'required|uuid', 'status' => 'required|in:pending,completed',
            'header' => 'required|array', 'header.bill_no' => 'required|string|max:255',
            'header.bill_date' => 'required|date_format:Y-m-d',
            'header.vendor_id' => 'nullable|integer', 'header.vendor_name' => 'required_without:header.vendor_id|nullable|string|max:255',
            'header.vendor_phone' => 'nullable|string|max:50',
            'header.document_type' => 'required|in:purchase,service,utility,credit_note,other',
            'header.grand_total' => 'required|numeric|min:0|max:99999999.99',
            'header.currency' => 'required|in:INR', 'header.extra_fields' => 'nullable|array',
            'header.remark' => 'nullable|string|max:5000',
            'header.extra_fields.discount_total' => 'nullable|numeric|min:0|max:99999999.99',
            'header.extra_fields.shipping_total' => 'nullable|numeric|min:0|max:99999999.99',
            'header.extra_fields.taxes' => 'nullable|array',
            'header.extra_fields.taxes.*.name' => 'required|string|max:255',
            'header.extra_fields.taxes.*.rate' => 'required|numeric|min:0|max:100',
            'header.extra_fields.taxes.*.amount' => 'required|numeric|min:0|max:99999999.99',
            'items' => 'required|array|min:1|max:500', 'items.*' => 'required|array',
            'items.*.name' => 'required|string|max:255', 'items.*.product_id' => 'nullable|integer',
            'items.*.category_id' => 'nullable|integer', 'items.*.category' => 'nullable|string|max:255',
            'items.*.hsn' => 'nullable|string|max:255', 'items.*.unit' => 'required|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.0001|max:99999999|decimal:0,4',
            'items.*.rate' => 'required|numeric|min:0|max:99999999.99',
            'items.*.amount' => 'required|numeric|min:0|max:99999999.99',
            'items.*.track_stock' => 'required|boolean', 'items.*.extra_fields' => 'nullable|array',
        ]);
        // Laravel filters array keys when nested rules exist. Keep unfamiliar
        // extra fields after validating the container and known financial keys.
        $data['header']['extra_fields'] = $request->input('header.extra_fields', []);
        $invoice = $save->save($data, $this->branchId(), (int) Auth::id());
        return response()->json([
            'success' => true, 'purchase_id' => $invoice->id,
            'message' => 'Bill saved. Products and stock updated; all reviewed details and the original bill are retained.',
            'redirect' => route('purchase.ai-bill.saved', $invoice->id),
        ]);
    }

    public function saved(int $id): View
    {
        $bill = AiPurchaseBill::where('branch_id', $this->branchId())->where('purchase_invoice_id', $id)->firstOrFail();
        return view('purchase.ai-bill-saved', ['bill' => $bill]);
    }

    public function original(int $id)
    {
        $bill = AiPurchaseBill::where('branch_id', $this->branchId())->findOrFail($id);
        return Storage::disk('local')->download($bill->file_path, $bill->original_name);
    }

    private function branchId(): int
    {
        $user = Auth::user();
        $selectedSubAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        return (int) $user->id;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function vendors(int $branchId)
    {
        return User::where('role', 'vendor')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'phone'])
            ->map(fn (User $vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'phone' => $vendor->phone,
            ])
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function categories(int $branchId)
    {
        return Category::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function products(int $branchId)
    {
        return Product::with('quantityType')->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'hsn_code', 'quantity', 'price', 'purchase_price', 'quantity_type_id'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'hsn_code' => $product->hsn_code,
                'quantity' => (float) $product->quantity,
                'unit' => $product->quantityType?->quantity_type ?? '',
                'price' => (float) ($product->purchase_price ?: $product->price),
            ])
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $vendors
     * @return array<string, mixed>|null
     */
    private function matchVendor(string $name, string $phone, $vendors): ?array
    {
        $matcher = app(ProductMatcher::class);
        $matches = $vendors->filter(function ($vendor) use ($name, $phone, $matcher) {
            return ($name !== '' && $matcher->normalize($vendor['name']) === $matcher->normalize($name))
                || ($phone !== '' && preg_replace('/\D+/', '', (string) $vendor['phone']) === $phone);
        });
        return $matches->count() === 1 ? $matches->first() : null;
    }
}
