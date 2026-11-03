# Flutter Desktop Sync Guide — Category, Brand & Quantity Type Linking

> **Backend:** `hotel-erp-admin-product` (Laravel)  
> **Live:** `hotel-erp.fableadtech.com`  
> **Flutter file:** `lib/core/services/sync_service.dart`  
> **Backend file:** `app/Http/Controllers/api/SyncController.php`  
> **Updated:** November 2026  

---

## 1. Problem (Fixed on Backend)

| Field on Desktop | Desktop Value | Website Before Fix |
|---|---|---|
| Category | `Italian` | Empty (`Select or Add Category`) |
| Brand | `Taco Bell` | OK (remote ID already existed) |
| Quantity Type | `Pcs` | Empty (`Select or Add Quantity Type`) |

### Why Brand worked
Server already had `Taco Bell` with a valid MySQL `id`. Desktop sent `brand_id: 1` and backend saved it.

### Why Quantity Type failed
- Online table is **`quantity_types`** (not `units`)
- Online FK is **`products.quantity_type_id`** (not `unit_id`)
- Desktop previously sent only `unit_id` / `units`
- Backend looked for `quantity_type_id` → got `null`

### Why Category failed
- New offline category had no online MySQL `remote_id`
- Desktop sent `category_client_uuid` / name
- Backend only used `category_id` → saved `NULL`

---

## 2. Backend Behavior (Already Implemented)

On **push sync**, for each product Laravel now resolves FKs in this order:

1. Valid remote numeric ID (must exist in MySQL)
2. `client_uuid`
3. Name (auto-create Category / Quantity Type if missing)

Helpers in `SyncController.php`:

- `resolveSyncedCategoryId()`
- `resolveSyncedQuantityTypeId()`
- `resolveSyncedBrandId()`

Also:

- Accepts root payload keys **`quantity_types`** and **`units`**
- Saves unit name into column **`quantity_types.quantity_type`** (not `type`)

---

## 3. What Flutter Must Send on Push

### 3.1 Root payload masters

```dart
final payload = <String, dynamic>{
  'categories': dirtyCategories,       // include new offline categories
  'brands': dirtyBrands,
  'quantity_types': dirtyQuantityTypes, // REQUIRED for Hotel ERP
  'units': dirtyQuantityTypes,          // alias — send BOTH
  'products': dirtyProducts,
  // ...other tables
};
```

> Always push **categories / brands / quantity_types before (or with) products** in the same push so UUIDs resolve.

### 3.2 Each product / raw-material map

```dart
Map<String, dynamic> mapProductForSync(Product row) {
  return {
    'client_uuid': row.clientUuid,
    'local_id': row.localId,
    'remote_id': row.remoteId, // MySQL id when known
    'id': row.remoteId,

    'name': row.name,
    'sku': row.sku,
    'SKU': row.sku,
    'price': row.price,
    'purchase_price': row.purchasePrice ?? row.costPrice,
    'quantity': row.quantity,
    'status': row.status ?? 'active',
    'availablility': row.availability ?? 'in_stock',
    'availability': row.availability ?? 'in_stock',
    'branch_id': row.branchId,
    'images': row.images,
    'isDeleted': row.isDeleted ? 1 : 0,

    // ----- CATEGORY (send all three) -----
    'category_id': row.categoryRemoteId,          // MySQL id if synced
    'category_client_uuid': row.categoryClientUuid,
    'category_name': row.categoryName,            // e.g. "Italian"
    // optional alias:
    // 'category': row.categoryName,

    // ----- QUANTITY TYPE / UNIT (send all aliases) -----
    'quantity_type_id': row.quantityTypeRemoteId, // MySQL id if synced
    'unit_id': row.quantityTypeRemoteId,          // alias for older paths
    'quantity_type_client_uuid': row.quantityTypeClientUuid,
    'unit_client_uuid': row.quantityTypeClientUuid,
    'quantity_type': row.quantityTypeName,        // e.g. "Pcs"
    'unit_name': row.quantityTypeName,

    // ----- BRAND (send all three) -----
    'brand_id': row.brandRemoteId,
    'brand_client_uuid': row.brandClientUuid,
    'brand_name': row.brandName,                  // e.g. "Taco Bell"
  };
}
```

### 3.3 Each quantity type / unit map

```dart
Map<String, dynamic> mapQuantityTypeForSync(QuantityType row) {
  return {
    'client_uuid': row.clientUuid,
    'local_id': row.localId,
    'remote_id': row.remoteId,
    'branch_id': row.branchId,
    'isDeleted': row.isDeleted ? 1 : 0,

    // Backend reads these keys (any one is enough; send all for safety)
    'quantity_type': row.name, // column on MySQL: quantity_types.quantity_type
    'type': row.name,
    'name': row.name,
    'unit_name': row.name,
  };
}
```

### 3.4 Each category map

```dart
Map<String, dynamic> mapCategoryForSync(Category row) {
  return {
    'client_uuid': row.clientUuid,
    'local_id': row.localId,
    'remote_id': row.remoteId,
    'name': row.name, // e.g. "Italian"
    'branch_id': row.branchId,
    'image': row.image,
    'isDeleted': row.isDeleted ? 1 : 0,
  };
}
```

---

## 4. What Flutter Must Read on Pull

When applying server products into Drift:

```dart
final quantityTypeId = p['quantity_type_id'] ?? p['unit_id'];
final categoryId = p['category_id'];
final brandId = p['brand_id'];

// Prefer nested relation names if present
final quantityTypeName = p['quantity_type']?['quantity_type']
    ?? p['quantity_type_name']
    ?? p['unit_name'];

final categoryName = p['category']?['name'] ?? p['category_name'];
final brandName = p['brand']?['name'] ?? p['brand_name'];
```

Store both:

- Local FK to Drift masters (by `client_uuid` / name)
- `remote_id` from server for next push

After push response, update local `remote_id` from `synced_mappings`:

```json
{
  "synced_mappings": {
    "categories": [{ "local_id": 12, "remote_id": 45, "client_uuid": "..." }],
    "quantity_types": [{ "local_id": 3, "remote_id": 8, "client_uuid": "..." }],
    "products": [{ "local_id": 99, "remote_id": 210, "client_uuid": "..." }]
  }
}
```

---

## 5. Important Rules for Flutter

1. **Never send only a local SQLite id as `category_id` / `quantity_type_id`** unless it is known to be the MySQL `remote_id`. If unsure, send `null` + `*_client_uuid` + `*_name`.
2. Always include **`category_name`**, **`quantity_type` / `unit_name`**, **`brand_name`** on product push.
3. Push **`quantity_types` and `units`** (same list) so backend master table is filled.
4. After creating a category/unit offline, set `is_synced = 0` / `is_dirty = true` on the **master and the product**.
5. On successful push, save returned `remote_id` back onto local masters and products.

---

## 6. API Endpoints

| Action | Method | Path |
|---|---|---|
| Delta push | `POST` | `/api/v1/sync/push` |
| Full push | `POST` | `/api/sync/full-push` |
| Delta pull | `POST` | `/api/v1/sync/pull` |
| Full pull | `GET` | `/api/sync/full-pull` |

Headers (typical):

```http
Authorization: Bearer <token>
X-License-Key: <client-license-key>
Content-Type: application/json
Accept: application/json
```

---

## 7. Re-Sync Checklist (QA)

1. Deploy latest Laravel `SyncController.php` to live.
2. Desktop → **Products** → Edit `BBQ Cottage Cheese` → **Submit** (`is_synced = 0`).
3. **Settings → Sync Settings → Sync All**.
4. Web Admin → Edit Product:
   - Category = **Italian**
   - Quantity Type = **Pcs**
   - Brand = **Taco Bell**

---

## 8. Quick Diff Checklist in `sync_service.dart`

- [ ] Root payload includes `'quantity_types'` **and** `'units'`
- [ ] Product map includes `category_id`, `category_client_uuid`, `category_name`
- [ ] Product map includes `quantity_type_id`, `unit_id`, `quantity_type_client_uuid`, `unit_client_uuid`, `quantity_type`, `unit_name`
- [ ] Product map includes `brand_id`, `brand_client_uuid`, `brand_name`
- [ ] Pull reads `quantity_type_id` (fallback `unit_id`)
- [ ] Push mappings update local `remote_id` for categories, quantity_types, products

---

## 9. Example Minimal Push Body (one product)

```json
{
  "categories": [
    {
      "client_uuid": "cat-uuid-italian",
      "local_id": 5,
      "name": "Italian",
      "branch_id": 1
    }
  ],
  "quantity_types": [
    {
      "client_uuid": "qt-uuid-pcs",
      "local_id": 2,
      "quantity_type": "Pcs",
      "name": "Pcs",
      "branch_id": 1
    }
  ],
  "units": [
    {
      "client_uuid": "qt-uuid-pcs",
      "local_id": 2,
      "quantity_type": "Pcs",
      "name": "Pcs",
      "branch_id": 1
    }
  ],
  "products": [
    {
      "client_uuid": "prod-uuid-bbq",
      "local_id": 88,
      "name": "BBQ Cottage Cheese",
      "sku": "BBQ-001",
      "price": 249,
      "branch_id": 1,
      "category_id": null,
      "category_client_uuid": "cat-uuid-italian",
      "category_name": "Italian",
      "quantity_type_id": null,
      "unit_id": null,
      "quantity_type_client_uuid": "qt-uuid-pcs",
      "unit_client_uuid": "qt-uuid-pcs",
      "quantity_type": "Pcs",
      "unit_name": "Pcs",
      "brand_id": 1,
      "brand_client_uuid": "brand-uuid-taco",
      "brand_name": "Taco Bell"
    }
  ]
}
```

After this push, website Edit Product should show **Italian**, **Pcs**, and **Taco Bell**.
