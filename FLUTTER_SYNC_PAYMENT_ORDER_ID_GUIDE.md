# Flutter Sync Push — Payment ↔ Order ID Mapping Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **API:** `POST /api/v1/sync/push`  
> **Tables:** `orders` · `order_items` · `payment_store` · `table_no`  
> **Audience:** Flutter Desktop / POS (Drift / SQLite offline)  
> **Last Updated:** November 2026  

---

## 1. Problem (why push returned 500)

Desktop creates an order with a **local SQLite id** (e.g. `68`). MySQL assigns a **different** auto-increment id (e.g. `79`).

If `payment_store.order_id` is sent as `68`, MySQL rejects it:

```text
SQLSTATE[23000]: Integrity constraint violation: 1452
FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
```

**Rule:** Never treat Desktop `orders.id` as the server MySQL `orders.id` unless it was already synced (`remote_id` known).

---

## 2. How the backend resolves order_id now

On each push, the server builds maps while saving `payload.orders[]`:

| Map | Key | Value |
|-----|-----|--------|
| Local → Remote | `orders[].local_id` (and payload `id` if no `remote_id`) | MySQL `orders.id` |
| UUID → Remote | `orders[].client_uuid` | MySQL `orders.id` |

Then for each `payment_store` / standalone `order_items` / `table_no` row it resolves in this order:

1. `local_order_id` (or `order_local_id`) → batch local map  
2. `order_id` → batch local map (in case Desktop still sent SQLite id)  
3. `order_client_uuid` → batch UUID map, else DB lookup by `orders.client_uuid`  
4. `order_id` / `remote_order_id` → **only if that id already exists in MySQL**  
5. Else → `order_id = null` (allowed by FK `ON DELETE SET NULL`) — **never** inserts an unverified local id  

Headers (always):

```http
POST /api/v1/sync/push
Authorization: Bearer {access_token}
X-License-Key: {client_license_unique_id}
Accept: application/json
Content-Type: application/json
```

---

## 3. What Flutter must send

### 3.1 Every order in `payload.orders[]`

| Field | Required | Notes |
|-------|----------|--------|
| `client_uuid` | **Yes** | Stable UUID for this order (generate once offline, never change) |
| `local_id` | **Yes** | Drift/SQLite primary key |
| `remote_id` | When known | MySQL id from a previous sync (`synced_mappings`) |
| `order_number` | Recommended | Helps dedupe |
| `items` / `order_items` | As needed | Nested items use parent MySQL id automatically |

Also send tax/money fields as in `FLUTTER_ORDER_SERVICE_TAX_GST_SYNC_GUIDE.md`.

### 3.2 Every payment in `payload.payment_store[]`

| Field | Required | Notes |
|-------|----------|--------|
| `client_uuid` | **Yes** | Stable UUID for this payment row |
| `local_id` | Recommended | Payment’s own SQLite id (returned in mappings) |
| `local_order_id` | **Yes** (for new orders) | Parent order’s **SQLite** id (same as `orders[].local_id` in this batch) |
| `order_client_uuid` | **Yes** | Parent order’s `client_uuid` (lookup from local DB — do not invent) |
| `order_id` | Only if synced | Send MySQL id **only** when `orders.remote_id` is known. For brand-new offline orders prefer `null` |
| `payment_amount` / `amount` | Yes | Total paid |
| `payment_method` | Yes | e.g. `cash`, `upi` |
| `payment_date` | Recommended | `YYYY-MM-DD` |
| `cash_amount` / `upi_amount` | Recommended | Split amounts |
| `remaining_amount` | Recommended | |
| `status` | Recommended | e.g. `paid` |
| `branch_id` | Recommended | |
| `remarks` | Optional | |

### 3.3 Standalone `order_items[]` (if not nested under orders)

Same parent resolution fields: `local_order_id`, `order_client_uuid`, and `order_id` only when remote is known.

---

## 4. Example push payload (new offline order + payment)

```json
{
  "branch_id": 1,
  "license_key": "CULZ4",
  "payload": {
    "orders": [
      {
        "local_id": 68,
        "client_uuid": "f5916ba6-6833-4b52-b66b-55e8ed6d8a86",
        "remote_id": null,
        "order_number": "2026111357277",
        "order_type": "Dine-In",
        "total_amount": 510,
        "remaining_amount": 0,
        "payment_status": "completed",
        "payment_method": "cash",
        "gst_option": "with_gst",
        "service_tax_rate": 0,
        "items": [
          {
            "local_id": 101,
            "client_uuid": "a1111111-1111-4111-8111-111111111111",
            "product_client_uuid": "p2222222-2222-4222-8222-222222222222",
            "product_name": "Masala Dosa",
            "quantity": 2,
            "price": 255,
            "total_amount": 510
          }
        ]
      }
    ],
    "payment_store": [
      {
        "local_id": 12,
        "client_uuid": "70617900-0001-4000-8000-000000000062",
        "local_order_id": 68,
        "order_client_uuid": "f5916ba6-6833-4b52-b66b-55e8ed6d8a86",
        "order_id": null,
        "user_id": 1,
        "payment_amount": 510,
        "amount": 510,
        "payment_date": "2026-11-13",
        "payment_type": "Fully",
        "payment_method": "cash",
        "cash_amount": 510,
        "upi_amount": 0,
        "remaining_amount": 0,
        "status": "paid",
        "branch_id": 1,
        "remarks": "Dine-In Checkout for TBL 1 (2026111357277)"
      }
    ]
  }
}
```

**Key points in the example:**

- `payment_store.local_order_id` = `68` matches `orders[0].local_id`  
- `payment_store.order_client_uuid` = same UUID as the order  
- `payment_store.order_id` = `null` because this order has no server `remote_id` yet  

---

## 5. Success response — apply `synced_mappings`

```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "synced_mappings": {
    "orders": [
      {
        "local_id": 68,
        "remote_id": 79,
        "client_uuid": "f5916ba6-6833-4b52-b66b-55e8ed6d8a86",
        "items": [
          {
            "local_id": 101,
            "remote_id": 501,
            "client_uuid": "a1111111-1111-4111-8111-111111111111"
          }
        ]
      }
    ],
    "payment_store": [
      {
        "local_id": 12,
        "remote_id": 33,
        "client_uuid": "70617900-0001-4000-8000-000000000062"
      }
    ]
  },
  "client": {
    "license_key": "CULZ4",
    "database": "u573967329_hotelerpdb"
  }
}
```

### Flutter must persist after success

```dart
// Pseudo-code
for (final m in response['synced_mappings']['orders']) {
  await ordersDao.setRemoteId(
    localId: m['local_id'],
    remoteId: m['remote_id'],
    clientUuid: m['client_uuid'],
  );
}
for (final m in response['synced_mappings']['payment_store']) {
  await paymentsDao.setRemoteId(
    localId: m['local_id'],
    remoteId: m['remote_id'],
    clientUuid: m['client_uuid'],
  );
}
```

On the **next** push for that payment, you may send:

```json
"order_id": 79
```

(because `79` is now a real MySQL id), still keep `order_client_uuid` as a safety net.

---

## 6. Recommended Flutter builder for payment rows

```dart
Map<String, dynamic> paymentToSyncJson(PaymentRow p, OrderRow order) {
  final hasServerOrderId = order.remoteId != null && order.remoteId! > 0;

  return {
    'local_id': p.id,
    'client_uuid': p.clientUuid,
    // Parent links — ALWAYS send these for new/offline bills
    'local_order_id': order.id,
    'order_client_uuid': order.clientUuid,
    // Only send MySQL FK when known
    'order_id': hasServerOrderId ? order.remoteId : null,
    'payment_amount': p.paymentAmount,
    'amount': p.paymentAmount,
    'payment_date': p.paymentDate, // yyyy-MM-dd
    'payment_type': p.paymentType ?? 'Fully',
    'payment_method': p.paymentMethod ?? 'cash',
    'cash_amount': p.cashAmount ?? 0,
    'upi_amount': p.upiAmount ?? 0,
    'remaining_amount': p.remainingAmount ?? 0,
    'status': p.status ?? 'paid',
    'branch_id': p.branchId ?? 1,
    'remarks': p.remarks,
    'user_client_uuid': p.userClientUuid, // optional but preferred
  };
}
```

### Push batch ordering

Always include parent orders in the **same** push payload as their payments:

```text
payload.orders[]          ← first (server processes first)
payload.payment_store[]   ← same request
```

Do **not** push payments in a later request unless the order already has `remote_id` (or at least `order_client_uuid` already exists on the server).

---

## 7. Pull note

On `POST /api/v1/sync/pull`, `payment_store` rows include server `order_id` (MySQL). When upserting locally:

1. Store server id as `remote_id`  
2. Resolve local parent via `orders.remote_id == payment.order_id` **or** via `order.client_uuid` if the API embeds it  
3. Keep `client_uuid` stable forever  

---

## 8. Verification checklist

| # | Check |
|---|--------|
| 1 | Create order offline → checkout → sync push once |
| 2 | Response is `200` / `status: success` (no 1452 FK error) |
| 3 | MySQL `orders` has the new row; note its `id` |
| 4 | MySQL `payment_store.order_id` equals that MySQL order id (or `NULL` only if order UUID missing) |
| 5 | Local Drift stores `remote_id` from `synced_mappings` |
| 6 | Second push does not create duplicate payments (`client_uuid` upsert) |
| 7 | License header present so data lands in the correct tenant DB |

---

## 9. Related guides

| Guide | Topic |
|-------|--------|
| `FLUTTER_LICENSE_TENANT_SYNC_GUIDE.md` | `X-License-Key` + per-client DB |
| `FLUTTER_ORDER_SERVICE_TAX_GST_SYNC_GUIDE.md` | Service Tax / GST on orders |
| `FLUTTER_DRIFT_SYNC_GUIDE.md` | Full Drift sync overview |

---

## 10. Backend reference (already deployed in SyncController)

- Builds `$localToRemoteOrderMap` / `$uuidToRemoteOrderMap` while saving orders  
- `resolveSyncedOrderId()` used by `payment_store`, standalone `order_items`, and `table_no`  
- Unresolved FK → `null` (no 500)  
