<?php

namespace App\Support;

use Illuminate\Support\Str;

class BillFieldLabel
{
    public static function labels(): array
    {
        return [
            'discount_total' => 'Discount', 'shipping_total' => 'Delivery charges',
            'taxes' => 'Taxes', 'vendor_address' => 'Supplier address',
            'vendor_name' => 'Supplier name', 'vendor_phone' => 'Supplier phone',
            'vendor_id' => 'Supplier', 'customer_name' => 'Customer name',
            'bill_no' => 'Bill number', 'bill_date' => 'Bill date',
            'document_type' => 'Bill type', 'grand_total' => 'Bill total',
            'items_total' => 'Items total', 'amount_in_words' => 'Amount in words',
            'previous_balance' => 'Previous balance', 'purchase_price' => 'Purchase price',
            'unit_price' => 'Price per unit', 'track_stock' => 'Update stock',
            'current_stock' => 'Current stock', 'quantity_type_id' => 'Unit',
            'category_id' => 'Category', 'product_id' => 'Product',
            'hsn_code' => 'HSN code', 'hsn' => 'HSN / SAC',
            'gstin' => 'GST number', 'ifsc_code' => 'IFSC code',
            'account_number' => 'Account number', 'a/c numer' => 'Account number',
            'propreiter' => 'Owner', 'proprietor' => 'Owner',
            'bags' => 'Bags / boxes', 'weight' => 'Weight',
            'name' => 'Name', 'rate' => 'Rate', 'amount' => 'Amount',
        ];
    }

    public static function format(string $key): string
    {
        $lookup = strtolower(trim($key));
        return static::labels()[$lookup] ?? Str::headline($key);
    }
}
