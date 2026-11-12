# Flutter Order Service Tax & GST Sync Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **APIs:** `POST /api/v1/sync/push` · `POST /api/v1/sync/pull`  
> **Feature:** Persist **Service Tax (%)** + **With GST / Without GST** on synced orders  
> **Audience:** Flutter Desktop / Mobile POS  
> **Last Updated:** November 2026  

Flutter uses **only sync pull + sync push** for order money fields. No separate tax API is required.

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

---

## 1. Why this matters

If Desktop pushes `service_tax_rate`, `tax_breakdown`, and `gst_option` but the server drops them:

| Symptom on Web `/tables-view` | Cause |
|-------------------------------|--------|
| Service Tax (%) shows `0` | `orders.service_tax_rate` is `null` |
| Update total too low (e.g. ₹790.50 vs ₹988.13) | Web recalculates without Service Tax |
| GST toggles as Without GST | `gst_option` overwritten / defaulted |

**Backend now saves these fields on both create and update** inside `payload.orders[]`.

---

## 2. Order fields Flutter must send on push

Inside `payload.orders[]`, include **all** of the following whenever the bill has Service Tax and/or GST mode:

| Field | Type | Example | Notes |
|-------|------|---------|--------|
| `gst_option` | string | `"with_gst"` / `"without_gst"` | Also accepts `"with"` → stored as `with_gst` |
| `gst_apply_mode` | string | `"product"` / `"overall"` | Defaults to `"product"` if missing |
| `overall_gst_rate` | number \| null | `9.0` or `null` | Used when mode is `overall` |
| `service_tax_rate` | number | `25.0` | Preferred key |
| `service_tax_percent` | number | `25.0` | Alias (accepted) |
| `service_tax_percentage` | number | `25.0` | Alias (accepted) |
| `service_tax_amount` | number | `197.625` | Rounded to 2 decimals on server |
| `service_tax_name` | string | `"Service Tax"` | Preferred key |
| `service_tax_label` | string | `"Service Tax"` | Alias (accepted) |
| `tax_breakdown` | array | see below | Prefer always sending when Service Tax > 0 |
| `tax_id` | string \| array | `"[]"` | Prefer `"[]"` (Web style). `"0.00"` is accepted and normalized |
| `discount` | number | `15.0` | Already synced |
| `total_amount` | number | `988.125` | Already synced |
| `remaining_amount` | number | `988.125` | Already synced |

### Recommended `tax_breakdown` shape

```json
[
  {
    "id": "service_tax",
    "name": "Service Tax",
    "rate": 25.0,
    "amount": 197.63,
    "kind": "service"
  }
]
```

If `tax_breakdown` is empty/missing but `service_tax_rate > 0`, the server **builds** this array automatically.

---

## 3. Push example (`POST /api/v1/sync/push`)

```http
POST /api/v1/sync/push
```

```json
{
  "branch_id": 1,
  "payload": {
    "orders": [
      {
        "local_id": 62,
        "remote_id": 80,
        "client_uuid": "aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee",
        "order_number": "2026111223101",
        "order_type": "Dine-In",
        "type": "dinein",
        "table_id": 27,
        "table_name": "TBL 2",
        "table_no": 27,
        "table_status": 1,
        "delivery_status": "running",
        "branch_id": 1,
        "discount": 15.0,
        "discount_percentage": 15.0,
        "discount_amount": 139.5,
        "tax_id": "[]",
        "tax_amount": 0.0,
        "gst_option": "with_gst",
        "gst_apply_mode": "product",
        "overall_gst_rate": null,
        "service_tax_rate": 25.0,
        "service_tax_amount": 197.625,
        "service_tax_name": "Service Tax",
        "tax_breakdown": [
          {
            "id": "service_tax",
            "name": "Service Tax",
            "rate": 25.0,
            "amount": 197.625,
            "kind": "service"
          }
        ],
        "subtotal": 930.0,
        "total_amount": 988.125,
        "remaining_amount": 988.125,
        "payment_status": "pending",
        "payment_method": "cash",
        "items": []
      }
    ]
  }
}
```

### What the server stores

| Field | Stored value |
|-------|----------------|
| `service_tax_rate` | `25.00` |
| `service_tax_amount` | `197.63` |
| `service_tax_name` | `Service Tax` |
| `tax_breakdown` | JSON array (service row) |
| `gst_option` | `with_gst` |
| `gst_apply_mode` | `product` |
| `overall_gst_rate` | `null` (or decimal) |
| `tax_id` | `"[]"` when Desktop sent `"0.00"` / empty |

### Identity rules

1. Prefer match by `client_uuid`.
2. If new UUID but `remote_id` / `id` exists on server → update that row.
3. Always keep `client_uuid` stable after first successful push.

---

## 4. Pull example (`POST /api/v1/sync/pull`)

```http
POST /api/v1/sync/pull
```

```json
{
  "branch_id": 1,
  "last_sync_timestamp": null,
  "tables": ["orders", "order_items", "settings", "table_no"]
}
```

### Orders in pull response

Each order in `data.orders[]` includes (when set):

```json
{
  "id": 80,
  "client_uuid": "aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee",
  "order_number": "2026111223101",
  "gst_option": "with_gst",
  "gst_apply_mode": "product",
  "overall_gst_rate": null,
  "service_tax_rate": "25.00",
  "service_tax_amount": "197.63",
  "service_tax_name": "Service Tax",
  "tax_breakdown": [
    {
      "id": "service_tax",
      "name": "Service Tax",
      "rate": 25,
      "amount": 197.63,
      "kind": "service"
    }
  ],
  "tax_id": "[]",
  "discount": "15.00",
  "total_amount": "988.13",
  "remaining_amount": "988.13"
}
```

**Flutter must upsert these fields into Drift** so reopen / reprint / settle use the same tax as Web.

### Settings in pull response (`data.settings[]`)

Use shop defaults for **new** bills only (not for already-synced orders):

| Field | Meaning |
|-------|---------|
| `service_tax_enabled` | `0` / `1` |
| `service_tax_rate` | e.g. `"10.00"` when configured |
| `service_tax_name` | e.g. `"Service Tax"` |
| `gst_apply_mode` | `"product"` / `"overall"` |
| `overall_gst_rate` | decimal or null |
| `default_gst_option` | shop default With/Without GST |

> If `settings[].service_tax_rate` is `null`, the value is **not configured in Web Settings** — fix it in Admin → Settings. Sync does not invent a rate.

---

## 5. Suggested Flutter Drift columns (`orders`)

```dart
// Add / keep on Orders table
TextColumn get gstOption => text().withDefault(const Constant('without_gst'))();
TextColumn get gstApplyMode => text().withDefault(const Constant('product'))();
RealColumn get overallGstRate => real().nullable()();
RealColumn get serviceTaxRate => real().nullable()();
RealColumn get serviceTaxAmount => real().nullable()();
TextColumn get serviceTaxName => text().nullable()();
TextColumn get taxBreakdownJson => text().nullable()(); // store JSON string
TextColumn get taxId => text().withDefault(const Constant('[]'))();
```

### Map into push payload

```dart
Map<String, dynamic> orderToSyncPayload(Order row) {
  return {
    'local_id': row.id,
    'remote_id': row.remoteId,
    'client_uuid': row.clientUuid,
    // ...existing fields...
    'gst_option': row.gstOption, // with_gst | without_gst
    'gst_apply_mode': row.gstApplyMode, // product | overall
    'overall_gst_rate': row.overallGstRate,
    'service_tax_rate': row.serviceTaxRate,
    'service_tax_amount': row.serviceTaxAmount,
    'service_tax_name': row.serviceTaxName ?? 'Service Tax',
    'tax_breakdown': row.taxBreakdownJson == null
        ? null
        : jsonDecode(row.taxBreakdownJson!),
    'tax_id': (row.taxId == null || row.taxId!.isEmpty) ? '[]' : row.taxId,
    'discount': row.discount,
    'total_amount': row.totalAmount,
    'remaining_amount': row.remainingAmount,
  };
}
```

### Map from pull response

```dart
OrdersCompanion orderFromPull(Map<String, dynamic> json) {
  return OrdersCompanion(
    // ...existing...
    gstOption: Value((json['gst_option'] ?? 'without_gst').toString()),
    gstApplyMode: Value((json['gst_apply_mode'] ?? 'product').toString()),
    overallGstRate: Value(_asDoubleOrNull(json['overall_gst_rate'])),
    serviceTaxRate: Value(_asDoubleOrNull(json['service_tax_rate'])),
    serviceTaxAmount: Value(_asDoubleOrNull(json['service_tax_amount'])),
    serviceTaxName: Value(json['service_tax_name']?.toString()),
    taxBreakdownJson: Value(
      json['tax_breakdown'] == null
          ? null
          : jsonEncode(json['tax_breakdown']),
    ),
    taxId: Value((json['tax_id'] ?? '[]').toString()),
    isDirty: const Value(false),
  );
}
```

---

## 6. Billing rules (Desktop ↔ Web parity)

1. **Running / printed table bill**  
   Prefer **order snapshot** fields (`service_tax_*`, `tax_breakdown`, `gst_*`) from DB/sync — do **not** overwrite with settings defaults on open.

2. **New blank bill**  
   Seed Service Tax % from `settings.service_tax_rate` only when `settings.service_tax_enabled == 1`.

3. **Totals**  
   Push the **same** `total_amount` / `remaining_amount` / `service_tax_amount` the UI shows. Server does not recalculate tax on sync push; it stores what Desktop sends.

4. **After push**  
   Mark order clean (`isDirty = false`) only after success; then Web `/tables-view` should show Service Tax % and Update total matching Desktop.

---

## 7. Verification checklist

1. On Desktop, create/update dine-in order with Service Tax `25` and With GST.
2. Sync push successfully.
3. Pull (or inspect DB) and confirm order has:
   - `service_tax_rate` = `25.00`
   - `service_tax_amount` set
   - `service_tax_name` = `Service Tax`
   - `tax_breakdown` with `kind: service`
   - `gst_option` = `with_gst`
   - `gst_apply_mode` = `product`
4. Open same table on Web ERP → Service Tax (%) = **25** and checkout total matches Desktop.

---

## 8. Backend reference (already deployed)

| File | Change |
|------|--------|
| `app/Http/Controllers/api/SyncController.php` | `applyOrderTaxFieldsFromSync()` on order create/update in push; settings normalization on pull |
| `app/Models/Order.php` | Fillable + casts for GST / Service Tax / `tax_breakdown` |

Aliases accepted on push:

- `service_tax_rate` ← `service_tax_percent` / `service_tax_percentage`
- `service_tax_name` ← `service_tax_label`
- `gst_option`: `with` → `with_gst`

---

## 9. Common mistakes

| Mistake | Result |
|---------|--------|
| Push only `total_amount` + `discount` | Web shows Service Tax 0 |
| Send `gst_option: true` / `1` | Treated as `without_gst` — use `"with_gst"` string |
| Clear local tax fields after pull | Next push wipes server tax |
| Use settings rate on an already-synced order open | Overwrites bill snapshot |

---

## 10. Quick contract summary

```
PUSH orders[]  →  must include gst_* + service_tax_* + tax_breakdown
PULL orders[]  →  Flutter must save those same fields locally
PULL settings[] → shop defaults for NEW bills only
```
