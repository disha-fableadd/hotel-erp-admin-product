# 🛠️ Backend Sync Guide: Include `product_name` in `/api/v1/sync/pull` for Order Items

## 1. Issue Summary

### Symptoms
* On the **Web Dashboard / Web POS**, product names show up properly (e.g. `Manchurian`, `Paneer Chilly`, `Garlic Naan`).
* On the **Desktop App**, after pulling orders from `/api/v1/sync/pull`, items sometimes showed as `null` or `Item` because `product_name` was missing from the `order_items` payload.
* Additionally, when `product_id` references the server's product ID, if `order_items` does not carry the actual string name, client devices that pull orders need extra joins or may fail to resolve names.

### Root Cause
1. **Web Frontend**: Laravel queries `$orderItem->product->name` or uses an SQL join with the `products` table on MySQL.
2. **Sync API Endpoint (`POST /api/v1/sync/pull`)**:
   In `SyncController.php`, when querying the `order_items` table or serializing `$order->items`, the raw `order_items` table is returned directly (`DB::table('order_items')->where(...)` or `$order->order_items`).
   Because the MySQL `order_items` table stores only `product_id` (foreign key) and not the string name, the serialized JSON output omits `product_name`:
   ```json
   {
     "id": 271,
     "order_id": 65,
     "product_id": 12,
     "price": 120.00,
     "quantity": 1,
     "total_amount": 120.00
   }
   ```
   *(Notice: `product_name` is missing from the object).*

---

## 2. Required Backend Fix

In `/home/u573967329/domains/hotel-erp.fableadtech.com/public_html/app/Http/Controllers/api/SyncController.php`:

### Solution A: When pulling `order_items` via Query Builder
Update the query in `pull()` where `order_items` are fetched:

```php
// BEFORE (Returns only raw order_items columns, missing product_name):
$orderItems = DB::table('order_items')
    ->where('branch_id', $branchId)
    ->where('updated_at', '>=', $lastSync)
    ->get();

// AFTER (LEFT JOIN products so product_name is always populated):
$orderItems = DB::table('order_items')
    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
    ->select(
        'order_items.*',
        DB::raw('COALESCE(products.name, order_items.product_name, "Item") as product_name')
    )
    ->where('order_items.branch_id', $branchId)
    ->where('order_items.updated_at', '>=', $lastSync)
    ->get();
```

### Solution B: If child items are nested inside `orders`
If your `pull()` endpoint nests items inside the order object (e.g. `$order['items']` or `$order['order_items']`), ensure each item carries `product_name`:

```php
foreach ($orders as $order) {
    if (!empty($order->items)) {
        foreach ($order->items as $item) {
            if (empty($item->product_name)) {
                $item->product_name = $item->product ? $item->product->name : ($item->name ?? 'Dish');
            }
        }
    }
}
```

### Expected API Payload:
```json
{
  "id": 271,
  "order_id": 65,
  "product_id": 12,
  "product_name": "Manchurian",
  "price": 120.00,
  "quantity": 1,
  "total_amount": 120.00
}
```

---

## 3. Client-Side Fixes Already Implemented in Flutter App

We have implemented automatic fallbacks in the Flutter Desktop App so that the app works seamlessly even before this backend update:

1. **Automatic Product Name Lookup by `remote_id` and `id`**:
   When `/api/v1/sync/pull` delivers an `order_item` where `product_name` is null or missing, the app looks up the local `products` table matching `p.remote_id == item['product_id']` and sets the product name automatically.
2. **Fixed SQLite Query in `getHotelTableActiveOrder`**:
   Fixed a SQLite syntax error where `"Item"` double-quotes were treated as a column name, causing table active order queries to fail and show `₹0.00` on occupied tables. Changed to single quotes `'Item'` and added multi-column coalesce (`p.name`, `oi.product_name`, `oi.product_gst_details`).
3. **Multi-Source Fallback in Cart & KOT**:
   Both Dine-In table order views and Kitchen KOT now resolve product names from:
   `oi.product_name` -> `products.name` (matching local id or remote id) -> `product_gst_details` -> `catalogProducts`.

