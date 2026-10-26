<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleReturnController extends Controller
{
    public function getSaleDetails(Request $request, $invoiceNumber)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $BranchId     = $user->branch_id;
        $branchIdToUse = $BranchId;

        
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userBranchId;

        if ($role === 'admin' && $selectedSubAdminId) {
            $branchIdToUse = $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userBranchId; // sub-admin branch
        }
        // dd($selectedSubAdminId);
        $order = Order::where('order_number', $invoiceNumber)->first();

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Fetch user manually
        $user = User::find($order->user_id);

        // Decode JSON array of tax IDs
        $taxIds = json_decode($order->tax_id ?? '[]', true);

        // Fetch multiple tax records
        // $taxes = TaxRate::whereIn('id', $taxIds)->where('branch_id', $selectedSubAdminId)->where('isDeleted', 0)->get();
        $taxes = TaxRate::whereIn('id', $taxIds)
        ->where('branch_id', $branchIdToUse)
        ->where('isDeleted', 0)
        ->get();

        // Fetch order items and related product info
        $items = OrderItem::where('order_id', $order->id)->get()->map(function ($item) {
            $product   = Product::find($item->product_id);
            $images    = json_decode($product->images ?? '[]', true);
            $imageUrls = $product->image_url ?? [];

            return [
                'id'                => $item->id,
                'product_id'        => $item->product_id, // ✅ Add this
                'product_name'      => $product->name ?? 'N/A',
                'product_image'     => $images[0] ?? null,
                'product_image_url' => $imageUrls[0] ?? null,
                'quantity'          => $item->quantity,
                'price'             => $item->price,
            ];
        });

        $orderData = [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'user_id'          => $order->user_id,
            'user_name'        => $user->name ?? 'N/A',
            'user_phone'       => $user->phone ?? '',
            'discount'         => $order->discount ?? 0,
            'total_amount'     => $order->total_amount ?? 0,
            'paid_amount'      => ($order->total_amount - ($order->remaining_amount ?? 0)),
            'remaining_amount' => $order->remaining_amount ?? 0,
        ];

        $settings         = \DB::table('settings')->where('branch_id', $selectedSubAdminId)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'order'             => $orderData,
            'items'             => $items,
            'taxes'             => $taxes,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
        ]);
    }

    public function return_sale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products'                 => 'required|array|min:1',
            'products.*.order_item_id' => 'required|exists:order_items,id',
            'products.*.quantity'      => 'required|numeric|min:0',
            'products.*.price'         => 'required|numeric|min:0',
            'products.*.subtotal'      => 'required|numeric|min:0',
            'discount'                 => 'nullable|numeric|min:0|max:100',
            'grand_total'              => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $authUser  = Auth::guard('api')->user();
            $createdBy = $authUser->id;

            $productIds = [];
            $quantities = [];
            $productMap = [];
            $order_id   = null;

            foreach ($request->products as $item) {
                $orderItem                          = OrderItem::findOrFail($item['order_item_id']);
                $productIds[]                       = $orderItem->product_id;
                $productMap[$item['order_item_id']] = $orderItem->product_id;
                $quantities[$orderItem->product_id] = $item['quantity'];
                $order_id                           = $orderItem->order_id;
            }

            $products = Product::whereIn('id', $productIds)->get();

            $oldItems      = OrderItem::where('order_id', $order_id)->get();
            $oldQuantities = $oldItems->pluck('quantity', 'product_id')->toArray();

            $subtotal = 0;
            foreach ($products as $product) {
                $productId = $product->id;
                $newQty    = $quantities[$productId] ?? 0;
                $oldQty    = $oldQuantities[$productId] ?? 0;

                $subtotal += $product->price * $newQty;

                $difference = $newQty - $oldQty;
                if ($difference > 0) {
                    $product->decrement('quantity', $difference);
                } elseif ($difference < 0) {
                    $product->increment('quantity', abs($difference));
                }
            }

            $discount       = $request->discount ?? 0;
            $discountAmount = $subtotal * ($discount / 100);
            $taxableAmount  = $subtotal - $discountAmount;

            $taxRates = TaxRate::where('status', 'active')->get();
            $taxIds   = $taxRates->pluck('id')->toArray();
            $taxJson  = json_encode($taxIds);

            $totalTax = 0;
            foreach ($taxRates as $tax) {
                $totalTax += $taxableAmount * ($tax->tax_rate / 100);
            }

            $order = Order::findOrFail($order_id);

            // Calculate new remaining_amount
            $oldTotalAmount = $order->total_amount;
            $oldRemaining   = $order->remaining_amount;
            $grandTotal     = $request->grand_total;

            // New remaining: previous remaining minus reduction in total
            $newRemaining = max(0, $oldRemaining - ($oldTotalAmount - $grandTotal));

            // Update order without touching branch_id or created_by
            $order->update([
                'discount'         => $discount,
                'discount_amount'  => $discountAmount,
                'tax_ids'          => $taxJson,
                'tax_amount'       => $totalTax,
                'subtotal'         => $subtotal,
                'total_amount'     => $grandTotal,
                'remaining_amount' => $newRemaining,
            ]);

            // Replace order items
            OrderItem::where('order_id', $order->id)->delete();
            foreach ($request->products as $item) {
                $productId = $productMap[$item['order_item_id']] ?? null;
                $product   = $products->firstWhere('id', $productId);
                if (! $product) {
                    continue;
                }

                OrderItem::create([
                    'order_id'     => $order->id,
                    'user_id'      => $order->user_id ?? null,
                    'category_id'  => $product->category_id ?? null,
                    'product_id'   => $product->id,
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'total_amount' => $item['subtotal'],
                    'created_by'   => $createdBy,
                    'branch_id'    => $product->branch_id ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order returned successfully',
                'data'    => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to return sale',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
