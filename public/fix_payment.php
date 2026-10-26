<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
use Illuminate\Support\Facades\DB;

DB::table('orders')->where('isDeleted', '!=', 1)->whereRaw('remaining_amount > 0 AND remaining_amount < total_amount')->update(['payment_status' => 'partially']);

DB::table('purchase_invoice')->where('isDeleted', '!=', 1)->whereRaw('remaining_amount > 0 AND remaining_amount < grand_total')->update(['status' => 'partially']);

$invoiceIds = DB::table('purchase_invoice')->where('isDeleted', '!=', 1)->whereRaw('remaining_amount > 0 AND remaining_amount < grand_total')->pluck('id');

if($invoiceIds->isNotEmpty()) {
    DB::table('purchases')->where('isDeleted', '!=', 1)->whereIn('invoice_id', $invoiceIds)->update(['payment_status' => 'partial', 'purchase_status' => 'partially']);
}

echo "Updated DB!";
