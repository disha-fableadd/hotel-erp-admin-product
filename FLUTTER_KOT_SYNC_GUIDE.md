# Flutter KOT Sync Guide (One API)

Flutter / desktop POS talks to the server through **one sync API only**.  
Place order, update items, KOT delta, mark-as-printed, kitchen status, checkout — all go in **`POST /api/v1/sync/push`** (and come back via **`POST /api/v1/sync/pull`**).

> Do **not** call separate web APIs (`order_sale`, `update_sale_order`, `/kitchen/kot/...`) from Flutter. Those are for the browser admin only.

Base URL (example): `https://hotel-erp.fableadtech.com`

---

## 1. The only APIs Flutter needs

| Method | Path | Use |
|--------|------|-----|
| `POST` | `/api/v1/sync/login` | Login → Bearer token |
| `POST` | `/api/v1/sync/verify-license` | License check |
| `POST` | `/api/v1/sync/pull` | Download orders + items + masters |
| `POST` | `/api/v1/sync/push` | Upload orders + items + KOT state |
| `POST` | `/api/v1/sync/upload-image` | Images (optional) |

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

Everything for KOT lives inside the **`payload.orders`** (+ nested **`items`**) of that single push.

---

## 2. One payload = place / update / KOT / checkout

Flutter keeps the full order in Drift. On every change, push the **same order** with updated fields.

| App action | What to put in the same `push` |
|------------|--------------------------------|
| **Place** (new table order) | New `orders[]` + all `items[]`, `delivery_status: "running"`, `table_status: 1`, `kot_printed_qty: 0` on items |
| **Update** (add/remove/qty) | Same `client_uuid` order, updated `items[]`, build local KOT delta, then print locally |
| **KOT printed** | Same push again with `kot_printed_qty = quantity` on printed lines (and clear `kot_fire_ticket`) |
| **Kitchen ready/served** | Same order, change `delivery_status` → `ready` / `served` |
| **Checkout** | `table_status: 0`, `payment_status: "completed"`, payments in `payment_store`, **no KOT reprint** |

No second endpoint. Just mutate local DB → `sync/push`.

---

## 3. Push request shape

```http
POST /api/v1/sync/push
```

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

You can also send standalone `payload.order_items` linked by `order_client_uuid` — same sync API.

Alias accepted for fire ticket: `kot_pending_cancels` (same JSON as `kot_fire_ticket`).

---

## 4. Field reference (all in that one push)

### Order

| Field | Meaning |
|-------|---------|
| `client_uuid` | Stable id (required) |
| `table_no` | Table **id** (int). `"Table 5"` also accepted |
| `table_status` | `1` occupied, `0` free after checkout |
| `delivery_status` | KOT kitchen status: `running` → `ready` → `served` |
| `payment_status` | `pending` while running; `completed` on checkout |
| `kot_fire_ticket` | Latest delta lines for kitchen (`action`: `new` \| `cancel`) |

### Order item

| Field | Meaning |
|-------|---------|
| `client_uuid` | Stable line id (required) |
| `quantity` | Current qty |
| `kot_printed_qty` | Qty already sent to kitchen (Petpooja) |
| `variant_id` | Size / variant |
| `addons` | `[{id, name, price, qty}]` |
| `unit_price` / `price` | Line unit price (variant + add-ons) |
| `user_id` | Customer/user; server falls back if missing |
| `category_id` | Falls back from product if missing |

**Pending kitchen qty (local):**

```text
pending = quantity - kot_printed_qty
```

- `pending > 0` → print `[NEW]` that many  
- `pending < 0` → print `[CANCEL]`  
- After print → set `kot_printed_qty = quantity` and **push again** (same API)

---

## 5. Flutter flow (still one API)

```text
┌─────────────────┐
│  Local Drift DB │  place / update / print / status / checkout
└────────┬────────┘
         │  POST /api/v1/sync/push   ← only network call for data
         ▼
┌─────────────────┐
│  Hotel ERP API  │  upserts by client_uuid
└────────┬────────┘
         │  POST /api/v1/sync/pull   ← get kitchen/other device updates
         ▼
┌─────────────────┐
│  Local Drift DB │
└─────────────────┘
```

### Place

1. Insert order + items locally (`kot_printed_qty = 0`, `delivery_status = running`, `table_status = 1`).
2. Build `kot_fire_ticket` = all lines as `action: "new"`.
3. `sync/push`.
4. Print KOT from `kot_fire_ticket` (local printer).
5. Set each item `kot_printed_qty = quantity`, clear `kot_fire_ticket`, `sync/push` again.

### Update (add one item, e.g. kadhi-khichdi)

1. Change local items.
2. Build `kot_fire_ticket` = **only this save’s changes** (not whole cart).
3. `sync/push`.
4. Print only fire ticket; then bump `kot_printed_qty`, clear ticket, `sync/push`.

### Checkout

1. Set `table_status = 0`, `payment_status = completed`, add `payment_store` rows.
2. `sync/push`.
3. Print **customer bill** locally — **do not** open KOT.

---

## 6. Pull (same sync family)

```http
POST /api/v1/sync/pull
```

```json
{
  "branch_id": 12,
  "last_sync_timestamp": "2026-11-04 10:00:00",
  "tables": ["orders", "order_items", "table_no", "products", "payment_store"]
}
```

Response `data.orders` / `data.order_items` include `delivery_status`, `kot_printed_qty`, `variant_id`, `addons`, etc. Upsert locally by `client_uuid`.

---

## 7. Push response (id mapping)

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

Save `remote_id` against each `client_uuid` in Drift.

---

## 8. Drift suggestions

**orders:** `client_uuid`, `remote_id`, `table_no`, `delivery_status`, `table_status`, `payment_status`, `kot_fire_ticket_json`, `dirty`  

**order_items:** `client_uuid`, `order_client_uuid`, `product_id`, `variant_id`, `addons_json`, `quantity`, `kot_printed_qty`, `unit_price`, `dirty`

Cart line key (variants/add-ons):

```text
{productId}-v{variantId}-a{addonIds}
```

---

## 9. Server rules (already handled)

1. Upsert by `client_uuid` (no wipe of unrelated lines if uuids match).
2. Missing `user_id` / `category_id` → safe fallbacks.
3. `table_no` string like `"Table 5"` → resolved to id.
4. `delivery_status` accepts `running` / `ready` / `served` (VARCHAR).
5. `kot_printed_qty` + `kot_fire_ticket` / `kot_pending_cancels` saved on the **same** push.
6. `variant_id` + `addons` stored for bill/KOT labels.

---

## 10. Checklist

- [ ] Flutter uses **only** `/api/v1/sync/push` + `/pull` (+ login/license)
- [ ] No calls to `order_sale` / `update_sale_order` / `/kitchen/kot/*` from the app
- [ ] Every order/item has `client_uuid`
- [ ] Place: `kot_printed_qty = 0` then after print push with printed qty
- [ ] Update: fire ticket = delta only; then mark printed via same push
- [ ] Checkout: bill only; `table_status = 0`
- [ ] Variants/add-ons always included on items

---

*One API: `/api/v1/sync/push` carries order + items + KOT printed state + fire ticket + kitchen status + checkout.*
