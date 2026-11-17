# Backend Implementation Report: Sync API Fixes & Parity

**Document Version:** 1.0  
**Target System:** Fablead Hotel ERP Backend (Laravel PHP / MySQL)  
**Modified File:** `app/Http/Controllers/api/SyncController.php`  
**API Endpoints:** `POST /api/v1/sync/push`, `POST /api/v1/sync/pull`  
**Date Applied:** November 17, 2026  

---

## 1. Overview of Changes

All 4 issues described in the Sync API Fixes & Parity Guide have been implemented directly in `app/Http/Controllers/api/SyncController.php`. No other core system or blade files were modified.

| Issue | Area | Status | Summary of Fix |
|---|---|---|---|
| **1. Order Items Duplication Loop** | `push()` & DB | **Resolved** | Added check for `'replace_items'` flag; upserting by `id`/`remote_id`, `client_uuid`, or existing order item match instead of blind inserts. Stale items are cleaned up. |
| **2. Table Status & Bill Printed Sync** | `push()` | **Resolved** | Orders persist `table_status`, `bill_printed`, `bill_printed_at`, and `order_lifecycle_status`. Added automatic table status parity (`blank` / `running` / `printed`) and safe partial updates for `table_no` without requiring `table_name`. |
| **3. Active Tables in `deleted_ids`** | `pull()` | **Resolved** | Active tables (`running`, `printed`, `reserved`, `blank`, or tables with an order) are never returned in `deleted_ids`/`deleted_records`. Any active table mistakenly flagged is auto-repaired. |
| **4. Walk-in Customer & Admin ID 1** | `push()` | **Resolved** | Ignored customer `user_id = 1` (Admin) for walk-in orders. Retains `user_id = null` and sets `customer_name = 'Unregistered Customer'`. |

---

## 2. Detailed Technical Breakdown

### 2.1 Issue 1: Order Items Duplication Loop
- **File Location:** `app/Http/Controllers/api/SyncController.php` (inside `push()` under orders processing loop & standalone order items)
- **Before:**
  ```php
  // Blind insert:
  $itemUuid = $it['client_uuid'] ?? (string) Str::uuid();
  $orderItem = \App\Models\OrderItem::firstOrNew(['client_uuid' => $itemUuid]);
  // If client_uuid was missing or client pushed items repeatedly, new UUIDs were generated and rows multiplied.
  ```
- **After:**
  1. Checks for `'replace_items'` flag on the order or items:
     ```php
     $replaceItems = !empty($o['replace_items']) || !empty($o['replaceItems']);
     if ($replaceItems) {
         \App\Models\OrderItem::where('order_id', $order->id)->delete();
     }
     ```
  2. If `$replaceItems` is false, finds existing item by `id`/`remote_id`, `client_uuid`, or matching existing item for this order by product:
     ```php
     if ($itemId) {
         $existingItem = \App\Models\OrderItem::where('id', $itemId)->first();
     }
     if (!$existingItem && $itemUuid) {
         $existingItem = \App\Models\OrderItem::where('client_uuid', $itemUuid)->first();
     }
     if (!$existingItem && !empty($order->id)) {
         if ($candidateProdId) {
             $existingItem = \App\Models\OrderItem::where('order_id', $order->id)->where('product_id', $candidateProdId)->first();
         } elseif ($candidateProdName !== '') {
             $existingItem = \App\Models\OrderItem::where('order_id', $order->id)->where('product_name', $candidateProdName)->first();
         }
     }
     ```
  3. Stale item deletion: Deletes items previously belonging to this order that are not in the incoming payload:
     ```php
     if (!$replaceItems && !empty($syncedItemIds)) {
         \App\Models\OrderItem::where('order_id', $order->id)
             ->whereNotIn('id', $syncedItemIds)
             ->delete();
     }
     ```
  4. Also added upsert-by-ID support to standalone `payload['order_items']` (section 7.5).

---

### 2.2 Issue 2: Table Status & Bill Printed Synchronization
- **File Location:** `app/Http/Controllers/api/SyncController.php`
- **Part 1: Order Lifecycle Fields & Automatic `table_no` Parity**
  ```php
  $isBillPrinted = !empty($o['bill_printed']) 
      || !empty($o['is_printed']) 
      || (($o['order_lifecycle_status'] ?? '') === 'bill_printed') 
      || ((int)($o['table_status'] ?? 0) === 3);

  if (isset($o['table_status'])) {
      $order->table_status = (int)$o['table_status'];
  } elseif ($isBillPrinted) {
      $order->table_status = 3;
  }

  if (isset($o['bill_printed'])) {
      $order->bill_printed = (int)$o['bill_printed'];
  } elseif ($isBillPrinted) {
      $order->bill_printed = 1;
  }

  if (isset($o['bill_printed_at'])) {
      $order->bill_printed_at = $o['bill_printed_at'];
  } elseif ($isBillPrinted && empty($order->bill_printed_at)) {
      $order->bill_printed_at = now();
  }

  if (Schema::hasColumn('orders', 'order_lifecycle_status') && isset($o['order_lifecycle_status'])) {
      $order->order_lifecycle_status = $o['order_lifecycle_status'];
  }
  $order->save();
  ```
  Immediately mirrors the order's state onto the linked `table_no` record:
  - If order payment is completed/closed: Table status is updated to `blank`, `table_status = 0`, and `order_id` is set to `null`.
  - If bill is printed: Table status is updated to `printed`, `table_status = 3`, `order_id = $order->id`, and `bill_printed_at = now()`.
  - If running: Table status is updated to `running`, `table_status = 1`, and `order_id = $order->id`.

- **Part 2: Safe Partial Updates for `table_no`**
  - Section 5 of `push()` now supports looking up tables by `id`, `remote_id`, `client_uuid`, or name.
  - Does NOT throw `Undefined array key 'table_name'` when clients push partial updates (e.g. status changes without re-sending `table_name`).
  - Sets `status = 'printed'` and `table_status = 3` whenever `bill_printed` or `table_status = 3` is pushed.

---

### 2.3 Issue 3: Active Tables Returned in `deleted_ids` in `/sync/pull`
- **File Location:** `app/Http/Controllers/api/SyncController.php` (inside `pull()`)
- **Fix:**
  ```php
  // Table No safety: active tables must never be treated as deleted
  if ($tableKey === 'table_no') {
      $rawStatus = strtolower(trim((string)($r->status ?? '')));
      $isTableActive = in_array($rawStatus, ['running', 'printed', 'reserved', 'blank'], true)
          || ((int)($r->table_status ?? 0) > 0)
          || !empty($r->order_id);

      if ($isDel && $isTableActive) {
          try {
              DB::table('table_no')->where('id', $r->id)->update([
                  'isDeleted' => 0,
                  'deleted_at' => null,
              ]);
              $r->isDeleted = 0;
              $r->deleted_at = null;
          } catch (\Throwable $e) {}
          $isDel = false;
      }
  }
  ```
  Active tables are returned in the active list (`$payloadData['table_no']`) and excluded from `deleted_ids`.

---

### 2.4 Issue 4: Walk-in / Unregistered Customers & Admin ID 1
- **File Location:** `app/Http/Controllers/api/SyncController.php` (under `orders` loop in `push()`)
- **Fix:**
  ```php
  // Resolve customer / user (walk-in/unregistered shouldn't attach Admin User ID 1)
  $customerId = $o['user_id'] ?? $o['customer_id'] ?? null;
  if (!$customerId && !empty($o['user_client_uuid'])) {
      $user = \App\Models\User::where('client_uuid', $o['user_client_uuid'])->first();
      $customerId = $user ? $user->id : null;
  }
  if ($customerId == 1) {
      // ID 1 is Admin, never a walk-in customer!
      $customerId = null;
  }
  $userId = $customerId;

  // Resolve customer name
  $customerName = $o['customer_name'] ?? $o['import_party_name'] ?? null;
  if (empty($customerName)) {
      if ($userId) {
          $foundUser = \App\Models\User::find($userId);
          $customerName = $foundUser ? $foundUser->name : 'Customer';
      } else {
          $customerName = 'Unregistered Customer';
      }
  }

  $order->user_id = $userId; // Stored as NULL for unregistered customers
  $order->customer_name = $customerName;
  ```
  Removed the default fallback to `auth()->id()` or Admin User ID 1 for walk-ins. `order_items` also avoids setting Admin User ID 1 when order customer is null.

---

## 3. Database Maintenance Queries Run

The following queries were executed on MySQL:
1. **Deduplication of order items:**
   ```sql
   DELETE FROM order_items 
   WHERE id NOT IN (
       SELECT max_id FROM (
           SELECT MAX(id) as max_id 
           FROM order_items 
           GROUP BY order_id, COALESCE(product_id, 0), LOWER(TRIM(product_name))
       ) as t
   );
   ```
2. **Un-deletion of active dining tables:**
   ```sql
   UPDATE table_no 
   SET isDeleted = 0, deleted_at = NULL 
   WHERE id IN (27, 28, 29, 34);
   ```

---

## 4. Verification & Testing

### Verification Command (cURL)
To test bill printed status on Table 2 (Table ID 27, Order ID 280):

```bash
curl -X POST http://localhost/api/v1/sync/push \
  -H "Content-Type: application/json" \
  -H "User-Agent: FableadHotelERP/1.0" \
  -d '{
    "license_key": "CULZ4",
    "branch_id": 1,
    "payload": {
      "table_no": [
        {
          "id": 27,
          "remote_id": 27,
          "client_uuid": "7f341dce-f576-4b65-90c7-433e7120a519",
          "branch_id": 1,
          "table_name": "Table 2",
          "table_type": "AC",
          "table_status": 3,
          "status": "printed",
          "bill_printed": 1,
          "is_printed": 1,
          "order_lifecycle_status": "bill_printed",
          "order_id": 280,
          "is_deleted": 0
        }
      ]
    }
  }'
```

### Result in Web ERP (`/tables-view`):
- Table Card: `table-box table-card printed-table`
- Badge: `[Bill Printed]` (Green)
- Data attribute: `data-table-status="printed"`
