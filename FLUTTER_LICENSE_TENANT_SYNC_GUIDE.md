# Flutter License + Client Tenant Sync Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **Central panel:** `admin-client` / `admin_erp` (client licenses & DB credentials)  
> **APIs:** `verify-license` · `login` · `pull` · `push`  
> **Goal:** Every Desktop sync hits **only that client's database** via product license  
> **Last Updated:** November 2026  

---

## 1. How it works

```
Flutter Desktop
   │  X-License-Key: 23B8A   (admin-client setups.unique_id)
   ▼
Hotel ERP Sync API  (admin-product)
   │  TenantManager → POST {TENANT_API_URL}/resolve-license
   ▼
admin-client / admin_erp
   │  returns client + MySQL credentials for THIS product
   ▼
Hotel ERP switches DB connection → that client's data only
   │
   ▼
pull / push run on that tenant DB
```

When `TENANCY_ENABLED=true` on the Hotel ERP server:

| Rule | Detail |
|------|--------|
| License **required** | Missing key → `403` / `LICENSE_REQUIRED` |
| Resolve every sync | Pull/push call admin-client and activate that client DB |
| Product scoped | Only clients that purchased this Hotel product/project |
| Expired / inactive | `403` with `EXPIRED` / `INACTIVE` |

When tenancy is **disabled** (local single-DB), license is optional and the default `.env` database is used.

---

## 2. Pass the license on every call

Use the **same** key registered for the hotel client in admin-client (`unique_id` / license key).

### Preferred: HTTP header

```http
X-License-Key: 23B8A
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

### Also accepted

| Source | Keys |
|--------|------|
| Headers | `X-License-Key`, `X-Tenant-Key`, `X-Purchase-Code` |
| Body | `license_key`, `unique_id`, `purchase_code`, `tenant_license` |

Optional body flag: `"fresh_license": true` → skip local cache and re-check admin-client (use after renew/suspend).

---

## 3. Verify license (first launch)

```http
POST /api/v1/sync/verify-license
```

```json
{
  "license_key": "23B8A"
}
```

**Success (200):**

```json
{
  "status": true,
  "message": "License is active and valid.",
  "tenancy_enabled": true,
  "client": {
    "id": 12,
    "tenant_id": 12,
    "client_id": 12,
    "client_name": "Grand Palace Hotel",
    "brand_name": "Grand Palace",
    "domain": "grandpalace.fableadtech.com",
    "plan_name": "Hotel Master Plan",
    "status": "Active",
    "membership_status": "Active",
    "expiry_date": "2027-10-30",
    "max_user": 25,
    "max_branch": 2,
    "product_code": "HOTEL",
    "project_code": "HOTEL",
    "license_key": "23B8A",
    "database": "u12_hotelerpdb"
  },
  "server_time": "2026-11-12 17:00:00"
}
```

**Errors:**

| HTTP | `error` | Meaning |
|------|---------|---------|
| 422 | `LICENSE_REQUIRED` | Key missing |
| 403 | `EXPIRED` / `INACTIVE` / `NOT_FOUND` | Invalid or blocked in admin-client |
| 403 | `PRODUCT_MISMATCH` | License not for Hotel product |
| 503 | `UNREACHABLE` | admin-client API down (and no stale cache) |

Store `client` locally after success (name, expiry, max_branch) for UI.

---

## 4. Login (on that client's DB)

```http
POST /api/v1/sync/login
X-License-Key: 23B8A
```

```json
{
  "email": "cashier@hotel.com",
  "password": "secret",
  "license_key": "23B8A"
}
```

Response includes `token`, `user`, and `client` (same shape as verify).

> Login users exist **inside** the resolved client database. Wrong license → wrong DB → “Invalid credentials” even if the password is correct for another hotel.

---

## 5. Pull (client-scoped)

```http
POST /api/v1/sync/pull
X-License-Key: 23B8A
Authorization: Bearer {token}
```

```json
{
  "license_key": "23B8A",
  "branch_id": 1,
  "last_sync_timestamp": null,
  "tables": ["orders", "order_items", "products", "settings", "table_no"]
}
```

**Response includes:**

```json
{
  "status": "success",
  "server_time": "2026-11-12 17:05:00",
  "tenancy_enabled": true,
  "client": { "...same client object..." },
  "data": {
    "orders": [],
    "products": [],
    "settings": [],
    "deleted_ids": {},
    "deleted_records": {}
  }
}
```

Assert `client.id` matches the license you activated. If it changes, wipe local Drift for that hotel and full-pull again.

---

## 6. Push (client-scoped)

```http
POST /api/v1/sync/push
X-License-Key: 23B8A
Authorization: Bearer {token}
```

```json
{
  "license_key": "23B8A",
  "branch_id": 1,
  "payload": {
    "orders": [],
    "order_items": [],
    "table_no": []
  }
}
```

**Success response:**

```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "server_time": "2026-11-12 17:06:00",
  "tenancy_enabled": true,
  "client": { "...same client object..." },
  "synced_mappings": {
    "orders": [],
    "order_items": []
  }
}
```

---

## 7. Flutter implementation checklist

1. **Onboarding:** ask for License Key → call `verify-license` → save key securely (`flutter_secure_storage`).
2. **Every request interceptor:** attach `X-License-Key` + `Authorization`.
3. **Also put `license_key` in JSON body** (backup if proxies strip custom headers).
4. **On `LICENSE_REQUIRED` / `EXPIRED` / `INACTIVE`:** lock POS and force re-activation screen.
5. **On `client.id` change after pull:** clear local DB for previous tenant, then full pull.
6. **Do not mix hotels** in one Drift file — use `client.id` in DB path or meta key `tenant_id`.

### Suggested Drift meta keys

```dart
// SyncMetaTable
'license_key' → '23B8A'
'client_id'   → '12'
'client_name' → 'Grand Palace Hotel'
'expiry_date' → '2027-10-30'
```

### Dio interceptor sketch

```dart
dio.interceptors.add(InterceptorsWrapper(
  onRequest: (options, handler) async {
    final key = await secureStorage.read(key: 'license_key');
    if (key != null && key.isNotEmpty) {
      options.headers['X-License-Key'] = key;
      if (options.data is Map) {
        options.data = {
          ...Map<String, dynamic>.from(options.data as Map),
          'license_key': key,
        };
      }
    }
    final token = await secureStorage.read(key: 'access_token');
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  },
));
```

---

## 8. Server config (admin-product `.env`)

```env
TENANCY_ENABLED=true
TENANT_API_URL=https://admin-client.example.com/api/tenant
TENANT_API_SECRET=your-shared-secret-min-32-chars
TENANT_PRODUCT_CODE=HOTEL
TENANT_PROJECT_CODE=HOTEL
```

| Setting | Purpose |
|---------|---------|
| `TENANT_API_URL` | admin-client tenant API base (must expose `/resolve-license`) |
| `TENANT_API_SECRET` | Shared secret (`X-Tenant-Secret`) |
| `TENANT_PRODUCT_CODE` | Only clients that bought this product |

---

## 9. Flow for Flutter team (happy path)

```
1. verify-license(license_key)     → client info OK
2. login(email, password, key)     → token + client
3. pull(key, last_sync=null)       → full hotel masters/orders for THAT client
4. offline work in Drift
5. push(key, payload)              → writes into THAT client DB only
6. pull(key, last_sync=server_time)→ delta
```

---

## 10. Common mistakes

| Mistake | Result |
|---------|--------|
| Sync without `X-License-Key` (tenancy on) | `LICENSE_REQUIRED` |
| License of Hotel A, login user of Hotel B | Invalid credentials / empty data |
| Reuse one Drift DB for two licenses | Cross-hotel data corruption |
| Ignore `expiry_date` | Sudden `EXPIRED` on next sync |
| Assume Web URL alone selects client | On central host, **license key** selects the DB |

---

## 11. Related docs

- `FLUTTER_DRIFT_SYNC_GUIDE.md` — full table map + Drift schemas  
- `FLUTTER_ORDER_SERVICE_TAX_GST_SYNC_GUIDE.md` — Service Tax / GST on orders  
- `FLUTTER_KOT_SYNC_GUIDE.md` — KOT fields on push/pull  
- `FLUTTER_TABLES_SYNC_GUIDE.md` — table statuses  

---

## 12. Quick contract

```
EVERY sync API  →  X-License-Key (or license_key / unique_id)
TENANCY ON      →  admin-client resolve-license → that client's MySQL only
RESPONSES       →  include "client" { id, name, plan, expiry, ... }
```
