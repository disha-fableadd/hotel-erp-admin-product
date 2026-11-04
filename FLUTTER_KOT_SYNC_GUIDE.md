# Flutter KOT Sync Guide

Flutter uses **only these two APIs** for orders + KOT.

Base URL (example): `https://hotel-erp.fableadtech.com`

| Method | Path | Use |
|--------|------|-----|
| `POST` | `/api/v1/sync/pull` | Download orders + items + masters |
| `POST` | `/api/v1/sync/push` | Upload orders + items + KOT state |

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

---

## 1. Pull

```http
POST /api/v1/sync/pull
```

### Request

```json
{
  "branch_id": 12,
  "last_sync_timestamp": "2026-11-04 10:00:00",
  "tables": ["orders", "order_items", "table_no", "products", "payment_store"]
}
```

| Field | Notes |
|-------|--------|
| `branch_id` | Current branch |
| `last_sync_timestamp` | Incremental sync (ISO / `Y-m-d H:i:s`) |
| `tables` | Optional filter; omit to pull all mapped tables |

### Response (shape)

```json
{
  "status": true,
  "server_time": "2026-11-04 13:00:00",
  "data": {
    "orders": [],
    "order_items": [],
    "table_no": [],
    "products": [],
    "payment_store": [],
    "deleted_ids": {},
    "deleted_records": {}
  }
}
```

Upsert locally by `client_uuid`. Apply `deleted_ids` / `deleted_records` to remove rows.

Order / item fields you care about for KOT:

- `orders.delivery_status` — `running` \| `ready` \| `served` \| …
- `orders.table_status` — `1` occupied, `0` free
- `orders.kot_pending_cancels` / fire ticket JSON (if present)
- `order_items.quantity`, `kot_printed_qty`, `variant_id`, `addons`, `unit_price`

---

## 2. Push

```http
POST /api/v1/sync/push
```

One push covers: **place**, **update**, **KOT printed**, **kitchen status**, **checkout**.

### Request

```json
{
  "branch_id": 12,
  "payload": {
    "orders": [
      {
        "local_id": 101,
        "client_uuid": "1d0db22c-8201-46dd-a1d0-530a10686568",
        "order_number": "INV-10383196",
        "customer_name": "Unregistered Customer",
        "customer_phone": null,
        "table_no": 1,
        "order_type": "Dine-In",
        "type": "dinein",
        "delivery_status": "running",
        "table_status": 1,
        "payment_status": "pending",
        "payment_method": "pending",
        "discount": 0,
        "total_amount": 700,
        "remaining_amount": 700,
        "branch_id": 12,
        "kot_fire_ticket": [
          {
            "product_id": 22,
            "name": "kadhi-khichdi",
            "qty": 1,
            "action": "new",
            "station": "General"
          }
        ],
        "items": [
          {
            "local_id": 501,
            "client_uuid": "774990a1-68cc-40c3-8a32-38c7063b1049",
            "product_id": 10,
            "product_client_uuid": "prod-uuid-here",
            "category_id": 5,
            "variant_id": 3,
            "addons": [
              { "id": 1, "name": "Extra Cheese", "price": 30, "qty": 1 }
            ],
            "quantity": 2,
            "kot_printed_qty": 1,
            "price": 100,
            "unit_price": 100,
            "total_amount": 200,
            "quantity_type_id": 1,
            "user_id": 1,
            "isDeleted": 0
          }
        ]
      }
    ],
    "payment_store": []
  }
}
```

`kot_pending_cancels` is accepted as an alias of `kot_fire_ticket`.  
Standalone `payload.order_items` (with `order_client_uuid`) is also OK.

### What each app action puts in the same push

| Action | In `payload.orders` / items |
|--------|-----------------------------|
| Place | New order + items, `delivery_status: "running"`, `table_status: 1`, `kot_printed_qty: 0` |
| Update | Same `client_uuid`, updated `items`, `kot_fire_ticket` = **delta only** |
| KOT printed | Same order, set `kot_printed_qty = quantity`, clear fire ticket |
| Kitchen ready/served | Change `delivery_status` → `ready` / `served` |
| Checkout | `table_status: 0`, `payment_status: "completed"`, fill `payment_store` (no KOT) |

### Response

```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "server_time": "2026-11-03 17:40:57",
  "synced_mappings": {
    "orders": [
      {
        "local_id": 101,
        "remote_id": 19,
        "client_uuid": "1d0db22c-8201-46dd-a1d0-530a10686568",
        "items": [
          {
            "local_id": 501,
            "remote_id": 88,
            "client_uuid": "774990a1-68cc-40c3-8a32-38c7063b1049"
          }
        ]
      }
    ],
    "order_items": [
      {
        "local_id": 501,
        "remote_id": 88,
        "client_uuid": "774990a1-68cc-40c3-8a32-38c7063b1049"
      }
    ]
  }
}
```

Save `remote_id` ↔ `client_uuid` in Drift after every push.

---

## 3. KOT fields (inside push/pull only)

| Field | Where | Meaning |
|-------|--------|---------|
| `delivery_status` | order | `running` → `ready` → `served` |
| `table_status` | order | `1` running table, `0` after checkout |
| `kot_fire_ticket` | order | Latest NEW/CANCEL lines for kitchen print |
| `quantity` | item | Current qty |
| `kot_printed_qty` | item | Already sent to kitchen |
| `variant_id` / `addons` | item | Size + extras (price + labels) |

Local delta:

```text
pending = quantity - kot_printed_qty
```

After print → set `kot_printed_qty = quantity` → **push** again.

---

## 4. Flow

```text
Local change (place / update / print / status / checkout)
        │
        ▼
POST /api/v1/sync/push
        │
        ▼
POST /api/v1/sync/pull   (other devices / kitchen updates)
```

---

## 5. Checklist

- [ ] Only `/api/v1/sync/pull` and `/api/v1/sync/push`
- [ ] Every order/item has stable `client_uuid`
- [ ] Place: `kot_printed_qty = 0`; after print push with printed qty
- [ ] Update: `kot_fire_ticket` = this save’s changes only
- [ ] Checkout: bill only; `table_status = 0`
- [ ] Always send `variant_id` + `addons` when used
