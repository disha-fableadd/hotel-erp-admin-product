<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Purchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    // public function create_purchase_return(Request $request)
    // {
    //     $invoices = PurchaseInvoice::select('id', 'invoice_number')->get();

    //     return view('purchasereturn.createpurchasereturn', compact('invoices'));
    // }
    // public function create_purchase_return(Request $request)
    // {
    //     $invoices = PurchaseInvoice::select('id', 'invoice_number')
    //         ->where('isDeleted', 0)
    //         ->get();

    //     $settings = \DB::table('settings')->first();
    //     $currencySymbol = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     return view('purchasereturn.createpurchasereturn', compact(
    //         'invoices',
    //         'currencySymbol',
    //         'currencyPosition'
    //     ));
    // }

    // public function edit_purchase_return(Request $request)
    // {
    //     return view('purchasereturn/editpurchasereturn');
    // }
    // public function purchase_return_list(Request $request)
    // {
    //     return view('purchasereturn/purchasereturnlist');
    // }
    public function getVendorName($invoiceId)
    {
        $invoice = PurchaseInvoice::with('vendor')->find($invoiceId);

        if ($invoice && $invoice->vendor && $invoice->vendor->role === 'vendor') {
            return response()->json(['vendor_name' => $invoice->vendor->name]);
        }

        return response()->json(['vendor_name' => null]);
    }

    public function getInvoiceProducts($invoiceId)
    {
        $purchases = Purchases::with('product.category')
            ->where('invoice_id', $invoiceId)
            ->get();

        // Calculate subtotal from purchase items
        $subtotal = $purchases->sum(fn($p) => $p->price * $p->quantity);

        // Get invoice info
        $invoice = PurchaseInvoice::with('vendor')->find($invoiceId);
        if (! $invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $shipping = $invoice->shipping ?? 0.00;

        // Use saved taxes from the purchase_invoice table (JSON field)
        $taxDetails = json_decode($invoice->taxes, true) ?? [];

        // Calculate total tax amount from saved taxes
        $totalTaxAmount = collect($taxDetails)->sum(function ($tax) {
            return (float) $tax['amount'];
        });

        $grandTotal = $subtotal + $totalTaxAmount + $shipping;

        // Prepare product list
        $products = $purchases->map(function ($purchase) {
            $images    = $purchase->product->images ?? null;
            $imageList = is_string($images) ? json_decode($images, true) : [];
            $imagePath = rtrim(env('ImagePath'), '/') . '/' . (
                ! empty($imageList[0])
                    ? 'storage/' . $imageList[0]
                    : 'admin/assets/img/product/noimage.png'
            );

            $imageUrl = $imagePath;
            $basePath = env('ImagePath', '/'); // from .env
                                               // Full image URLs (array)
            $imageUrls = [];
            if (! empty($imageList)) {
                $imageUrls = array_map(function ($img) use ($basePath) {
                    return url(rtrim($basePath, '/') . '/storage/' . ltrim($img, '/'));
                }, $imageList);
            } else {
                $imageUrls = [url(rtrim($basePath, '/') . '/admin/assets/img/product/noimage.png')];
            }

            return [
                'product_name' => $purchase->product->name ?? 'N/A',
                'category'     => $purchase->product->category->name ?? 'N/A',
                'price'        => number_format($purchase->price, 2),
                'quantity'     => $purchase->quantity,
                'subtotal'     => number_format($purchase->price * $purchase->quantity, 2),
                'image'        => $imageUrl,
                'image_url'    => $imageUrls,
                'product_id'   => $purchase->item,
                'purchase_id'  => $purchase->id,
            ];
        });

        $settings         = \DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $totals = [
            'subtotal'    => number_format($subtotal, 2),
            'taxes'       => number_format($totalTaxAmount, 2),
            'shipping'    => number_format($shipping, 2),
            'grand_total' => number_format($grandTotal, 2),
        ];

        return response()->json([
            'products'         => $products,
            'invoice'          => $invoice,
            'vendor'           => $invoice->vendor,
            'totals'           => $totals,
            'taxDetails'       => $taxDetails, // already formatted in DB
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
        ]);
    }

    // public function updateQuantity(Request $request)
    // {
    //     $updates = $request->input('updates', []);

    //     foreach ($updates as $update) {
    //         $purchase = Purchases::find($update['purchase_id']);
    //         $product = Product::find($update['product_id']);
    //         $newQty = $update['quantity']; // New updated quantity

    //         if ($purchase && $product) {
    //             $oldQty = $purchase->quantity;

    //             // Calculate the quantity difference
    //             $difference = $newQty - $oldQty;

    //             // Update the purchase quantity
    //             $purchase->quantity = $newQty;
    //             $purchase->save();

    //             // Update product stock accordingly
    //             $product->quantity += $difference;
    //             $product->save();
    //         }
    //     }
    //     // dd($updates);

    //     return response()->json(['success' => true]);
    // }
    public function updateQuantity(Request $request)
    {
        $updates = $request->input('updates', []);

        DB::beginTransaction();
        try {
            foreach ($updates as $update) {
                $purchase = Purchases::find($update['purchase_id']);
                $product  = Product::find($update['product_id']);
                $newQty   = $update['quantity']; // New updated quantity

                if ($purchase && $product) {
                    $oldQty = $purchase->quantity;

                    // Calculate the quantity difference
                    $difference = $newQty - $oldQty;

                    // Update the purchase quantity
                    $purchase->quantity     = $newQty;
                    $purchase->amount_total = $newQty * $purchase->price;
                    $purchase->save();

                    // Update product stock accordingly
                    $product->quantity += $difference;
                    $product->save();

                    // Update the related invoice
                    $invoice = PurchaseInvoice::find($purchase->invoice_id);
                    if ($invoice) {
                        $products = json_decode($invoice->products, true);

                        foreach ($products as &$p) {
                            if ($p['product_id'] == $product->id) {
                                $p['quantity'] = $newQty;
                                $p['total']    = $newQty * $p['price'];
                            }
                        }

                        $invoice->products = json_encode($products);

                        // Recalculate totals
                        $totalAmount     = collect($products)->sum('total');
                        $grandTotal      = $totalAmount + ($invoice->shipping ?? 0) - ($invoice->discount ?? 0);
                        $remainingAmount = max(0, $grandTotal - ($invoice->paid ?? 0));

                        $invoice->total_amount     = $totalAmount;
                        $invoice->grand_total      = $grandTotal;
                        $invoice->remaining_amount = $remainingAmount;

                        $invoice->save();
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase quantities and invoice updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}
