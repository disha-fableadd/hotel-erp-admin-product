# Flutter Sync — Dashboard Metrics & Sales KPI Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **API:** `POST /api/v1/sync/dashboard-metrics`  
> **Also:** enriched `/api/v1/sync/pull` for `products` / `categories` / `orders`  
> **Audience:** Flutter Desktop / POS  
> **Last Updated:** November 2026  

---

## 1. Why this exists

Web ERP and Desktop were showing different numbers for:

| KPI | Web (source of truth) | Old Desktop |
|-----|------------------------|-------------|
| Today Total Sales | ₹7,799.10 | ₹1,969.20 (excluded pending) |
| Today Food Sales | ₹4,754.30 | incomplete |
| Today Liquor Share | ₹3,044.80 | missing |
| Sales Volume / invoices | include pending + partially | filtered |

**Fix:** Desktop should either:

1. Prefer **server metrics** from `dashboard-metrics` (recommended), or  
2. Mirror Web rules locally using enriched pull fields (`item_type`, `category_id`, lifecycle).

---

## 2. Authoritative metrics API

```http
POST /api/v1/sync/dashboard-metrics
Authorization: Bearer {access_token}
X-License-Key: {CULZ4}
Accept: application/json
Content-Type: application/json
```

### Request

```json
{
  "license_key": "CULZ4",
  "branch_id": 1,
  "date_filter": "all_time"
}
```

| Field | Values |
|-------|--------|
| `date_filter` | `today`, `yesterday`, `this_week`, `this_month`, `all_time`, `custom` |
| `start_date` / `end_date` | Required when `date_filter = custom` (`YYYY-MM-DD`) |
| `branch_id` | Same branch as Web KPI cards |

### Success `200`

```json
{
  "status": "success",
  "server_time": "2026-11-14T05:30:00.000000Z",
  "branch_id": 1,
  "data": {
    "today_metrics": {
      "today_total_sales": 7799.10,
      "today_food_sales": 4754.30,
      "today_liquor_share": 3044.80,
      "date": "2026-11-14",
      "timezone": "Asia/Kolkata",
      "day_start": "2026-11-14 00:00:00",
      "day_end": "2026-11-14 23:59:59",
      "orders_count": 8,
      "includes_pending_and_partial": true,
      "currency_symbol": "₹",
      "currency_position": "left"
    },
    "dashboard_metrics": {
      "total_sales_amount": 40772.87,
      "total_purchase_amount": 12830.00,
      "total_expense_amount": 0.00,
      "running_tables_count": 1,
      "sales_invoices_count": 56,
      "purchase_invoices_count": 12,
      "tables_count": 2,
      "vendors_count": 3,
      "date_filter": "all_time",
      "sales_volume_breakdown": {
        "total_orders": 56,
        "this_month": 40,
        "completed_count": 40,
        "completed_percent": 71.4,
        "partially_paid_count": 4,
        "partially_paid_percent": 7.1,
        "pending_count": 12,
        "pending_percent": 21.4
      }
    }
  },
  "client": {
    "license_key": "CULZ4",
    "database": "u573967329_hotelerpdb"
  }
}
```

### How Web computes today KPIs (same on this API)

1. Orders in IST day window: `created_at` between `day_start` and `day_end` (`Asia/Kolkata`, not UTC `CURDATE()`)  
2. Soft-delete: `isDeleted` is `0` / `'0'` / `null`  
3. **All** payment statuses (pending + partially + completed) — see `includes_pending_and_partial: true`  
4. Split bill `total_amount` by line items:  
   - `item_type` in `liquor|beverage|tobacco` **or** category `gst_applicable = false` → **Liquor**  
   - else → **Food**  
5. Food + Liquor always equals Total (paisa-rounded)  
6. Send the same `branch_id` Web uses (usually admin/sub-admin id, often `1`)

---

## 3. Flutter usage (recommended)

```dart
Future<void> refreshDashboardMetrics() async {
  final res = await api.post('/api/v1/sync/dashboard-metrics', body: {
    'license_key': licenseKey,
    'branch_id': branchId,
    'date_filter': 'all_time',
  });

  final today = res['data']['today_metrics'];
  final dash = res['data']['dashboard_metrics'];

  _todayTotalSales = (today['today_total_sales'] as num).toDouble();
  _todayFoodSales = (today['today_food_sales'] as num).toDouble();
  _todayLiquorShare = (today['today_liquor_share'] as num).toDouble();

  _totalSalesVolume = (dash['total_sales_amount'] as num).toDouble();
  _salesInvoices = dash['sales_invoices_count'] as int;
  _completedPct = (dash['sales_volume_breakdown']['completed_percent'] as num).toDouble();
  _partiallyPct = (dash['sales_volume_breakdown']['partially_paid_percent'] as num).toDouble();
  _pendingPct = (dash['sales_volume_breakdown']['pending_percent'] as num).toDouble();
}
```

Call after login, after sync pull/push, and on dashboard / tables screen open.

---

## 4. Pull enrichments (offline fallback)

### `categories[]`

| Field | Type | Notes |
|-------|------|--------|
| `item_type` | string | `food` / `liquor` / `beverage` / `tobacco` / `general` |
| `is_liquor` | 0\|1 | Convenience flag |
| `gst_applicable` | bool | Legacy liquor rule when `item_type` missing |

### `products[]`

| Field | Type | Notes |
|-------|------|--------|
| `category_id` | **int** | Always forced on pull (heal nulls) |
| `item_type` | string | Explicit or inherited from category |
| `resolved_item_type` | string | Same resolved value |
| `is_liquor` | 0\|1 | |

### `orders[]`

| Field | Type | Notes |
|-------|------|--------|
| `invoice_generated` | 0\|1 | Counts toward sales invoices |
| `invoice_number` | string\|null | From `order_invoice` or `order_number` |
| `order_lifecycle_status` | string | see below |

**`order_lifecycle_status` values:**

| Value | Meaning |
|-------|---------|
| `active_table` | Open dine-in (`table_status=1`, running) — hide from All Sales Bills until settled/printed |
| `bill_printed` | Bill printed or partially paid |
| `settled_invoice` | Completed / paid |
| `cancelled` | Cancelled / void |

### Sales Bills local filter (parity with Web)

```dart
bool showInSalesBills(Order o) {
  // Hide only active in-progress table sessions
  if (o.tableStatus == 1 && (o.deliveryStatus == 'running' || o.deliveryStatus == null)) {
    return false;
  }
  // Include pending + partially + completed
  return true;
}
```

Or use server field:

```dart
bool showInSalesBills(Order o) =>
  o.orderLifecycleStatus != 'active_table';
```

---

## 5. Deploy checklist (backend)

1. Deploy hotel code (SyncController + `DashboardMetricsService` + models)  
2. Run migration: `2026_11_14_113000_add_item_type_to_categories_and_products`  
3. Smoke test:

```http
POST /api/v1/sync/dashboard-metrics
X-License-Key: CULZ4
{ "branch_id": 1, "date_filter": "all_time" }
```

4. Confirm `today_total_sales` matches Web tables KPI  
5. Pull products → every row has integer `category_id` + `item_type`

---

## 6. Related guides

| File | Topic |
|------|--------|
| `FLUTTER_LICENSE_TENANT_SYNC_GUIDE.md` | License → correct client DB |
| `FLUTTER_SYNC_PAYMENT_ORDER_ID_GUIDE.md` | Payment ↔ order FK mapping |
| `FLUTTER_ORDER_SERVICE_TAX_GST_SYNC_GUIDE.md` | Service Tax / GST on orders |
