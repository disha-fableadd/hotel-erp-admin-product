# Flutter Tables Sync Guide

Flutter syncs dining tables through the **same sync APIs** used for KOT/orders.  
Optional list API is available for UI (floor map / All Tables).

Base URL (example): `https://hotel-erp.fableadtech.com`

| Method | Path | Use |
|--------|------|-----|
| `POST` | `/api/v1/sync/pull` | Download `table_no` (+ deletes) |
| `POST` | `/api/v1/sync/push` | Upload create / update / delete / status |
| `GET` | `/api/get-all-tables` | Full list for UI + `table_types` dropdown |

```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

---

## 1. Fields

| Field | Type | Notes |
|-------|------|--------|
| `id` | int | Server id (`remote_id`) |
| `client_uuid` | string | **Stable sync key** — upsert locally by this |
| `table_name` | string | e.g. `Table 1`, `Rooftop 12` |
| `table_type` | string | See types below |
| `table_status` | int | `0` blank, `1` running, `2` reserved, `3` printed |
| `status` | string | `blank` \| `running` \| `reserved` \| `printed` |
| `order_id` | int\|null | Linked running order (optional) |
| `order_client_uuid` | string\|null | Prefer this on **push** to link order |
| `branch_id` | int\|null | Branch |
| `isDeleted` | `0`\|`1` | Soft delete (`1` = deleted) |
| `created_at` / `updated_at` | datetime | Server timestamps |

Send **either** `status` **or** `table_status` on push — server keeps both in sync.

### Table types (same as web Add Table)

```text
AC | Non AC | Premium | Rooftop
```

Same name is allowed across types in one branch  
(e.g. `Table 1` + `AC` and `Table 1` + `Rooftop`).  
Duplicate = same **name + type** in the same branch.

### Floor / status colours (Petpooja-style)

| `status` | `table_status` | UI |
|----------|----------------|-----|
| `blank` | `0` | Available (grey) |
| `running` | `1` | Occupied (green) |
| `reserved` | `2` | Reserved (orange) |
| `printed` | `3` | Bill printed (yellow) |

---

## 2. Drift schema (tables only)

```dart
// tableType: AC | Non AC | Premium | Rooftop
// tableStatus: 0 blank | 1 running | 2 reserved | 3 printed
class TableNoTable extends Table with SyncColumns {
  TextColumn get tableName => text().withLength(min: 1, max: 100)();
  TextColumn get tableType => text().nullable().withDefault(const Constant('AC'))();
  IntColumn get tableStatus => integer().withDefault(const Constant(0))();
  TextColumn get status => text().withDefault(const Constant('blank'))();
  IntColumn get orderId => integer().nullable()();
  TextColumn get orderClientUuid => text().nullable()();
  IntColumn get branchId => integer().nullable()();
}
```

Always upsert by `client_uuid`. Never rely on local auto-id across devices.

---

## 3. Pull

```http
POST /api/v1/sync/pull
```

### Request

```json
{
  "branch_id": 12,
  "last_sync_timestamp": "2026-11-05 10:00:00",
  "tables": ["table_no"]
}
```

| Field | Notes |
|-------|--------|
| `branch_id` | Current branch |
| `last_sync_timestamp` | Incremental sync (`Y-m-d H:i:s`); omit / null for full |
| `tables` | Use `["table_no"]` to pull tables only |

### Response (shape)

```json
{
  "status": "success",
  "server_time": "2026-11-05 13:00:00",
  "data": {
    "table_no": [
      {
        "id": 88,
        "client_uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "table_name": "Rooftop 12",
        "table_type": "Rooftop",
        "table_status": 0,
        "status": "blank",
        "order_id": null,
        "branch_id": 12,
        "isDeleted": 0,
        "created_at": "2026-11-05T12:00:00.000000Z",
        "updated_at": "2026-11-05T12:00:00.000000Z"
      }
    ],
    "deleted_ids": {
      "table_no": [42]
    },
    "deleted_records": {
      "table_no": [
        { "remote_id": 42, "client_uuid": "old-uuid-here" }
      ]
    }
  }
}
```

### Apply pull locally

1. Upsert each row in `data.table_no` by `client_uuid`
2. Remove / mark deleted using `deleted_ids.table_no` or `deleted_records.table_no`
3. Save `server_time` as next `last_sync_timestamp`

Soft-deleted tables are included in pull so deletes sync to Flutter.

---

## 4. Push

```http
POST /api/v1/sync/push
```

One push covers: **create**, **edit**, **status change**, **soft delete**.

### Request

```json
{
  "branch_id": 12,
  "payload": {
    "table_no": [
      {
        "local_id": 1,
        "client_uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "table_name": "Rooftop 12",
        "table_type": "Rooftop",
        "table_status": 0,
        "status": "blank",
        "order_id": null,
        "order_client_uuid": null,
        "branch_id": 12,
        "isDeleted": 0
      },
      {
        "local_id": 2,
        "client_uuid": "bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb",
        "table_name": "Table 5",
        "table_type": "AC",
        "status": "printed",
        "table_status": 3,
        "order_client_uuid": "order-uuid-when-occupied",
        "branch_id": 12,
        "isDeleted": 0
      }
    ]
  }
}
```

### What each app action puts in the same push

| Action | In `payload.table_no` |
|--------|------------------------|
| Add table | New `client_uuid`, `table_name`, `table_type` (`AC` / `Non AC` / `Premium` / `Rooftop`), `status: blank` |
| Edit name/type | Same `client_uuid`, updated `table_name` / `table_type` |
| Occupy | `status: running` / `table_status: 1`, set `order_client_uuid` |
| Reserve | `status: reserved` / `table_status: 2` |
| Bill printed | `status: printed` / `table_status: 3` |
| Free / checkout | `status: blank` / `table_status: 0`, clear `order_id` / `order_client_uuid` |
| Delete | Same `client_uuid`, `isDeleted: 1` |

### Response

```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "server_time": "2026-11-05 13:05:00",
  "synced_mappings": {
    "table_no": [
      {
        "local_id": 1,
        "remote_id": 88,
        "client_uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
      }
    ]
  }
}
```

Save `remote_id` ↔ `client_uuid` in Drift after every push.

---

## 5. List API (UI helper)

Use when you need a sorted full list + type dropdown without a delta sync.

```http
GET /api/get-all-tables?selectedSubAdminId=12
```

### Response

```json
{
  "status": true,
  "table_types": ["AC", "Non AC", "Premium", "Rooftop"],
  "data": [
    {
      "id": 10,
      "client_uuid": "…",
      "table_name": "Rooftop 1",
      "table_type": "Rooftop",
      "table_status": 0,
      "status": "blank",
      "order_id": null,
      "branch_id": 12,
      "created_at": "…",
      "updated_at": "…"
    }
  ]
}
```

- Sorted **naturally** within type (`1, 2, 3…` not `1, 10, 11…`)
- Bind Add Table type dropdown from `table_types`
- Still upsert locally by `client_uuid` if you cache these rows

### Optional REST CRUD (same Bearer token)

| Method | Path |
|--------|------|
| `POST` | `/api/table/create` |
| `GET` | `/api/table/{id}` |
| `PUT` | `/api/table/update/{id}` |
| `DELETE` | `/api/table/delete/{id}` |

Prefer **sync push/pull** for offline Flutter. Use REST only for online-only screens if needed.

Body for create/update:

```json
{
  "table_name": "Rooftop 12",
  "table_type": "Rooftop",
  "selectedSubAdminId": 12
}
```

---

## 6. Flow

```text
Local change (add / edit / occupy / print / free / delete)
        │
        ▼
POST /api/v1/sync/push   payload.table_no
        │
        ▼
POST /api/v1/sync/pull   tables: ["table_no"]   (other devices)
```

Web-created tables also get `client_uuid` automatically — next pull brings them to Flutter.

---

## 7. Checklist

- [ ] Tables go through `/api/v1/sync/pull` + `/api/v1/sync/push` (`payload.table_no`)
- [ ] Every table has a stable `client_uuid`
- [ ] Upsert + delete by `client_uuid` (use `deleted_ids` / `deleted_records`)
- [ ] `table_type` supports `AC`, `Non AC`, `Premium`, `Rooftop`
- [ ] Status supports `printed` / `3` (bill printed yellow)
- [ ] On occupy, send `order_client_uuid` when linking an order
- [ ] On free/checkout, clear link and set `blank` / `0`
- [ ] Optional UI list: `GET /api/get-all-tables` → use `table_types` + `data`
