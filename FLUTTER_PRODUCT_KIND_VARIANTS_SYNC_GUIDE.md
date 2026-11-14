# Flutter Sync — Product Kind, Food Type, Kitchen Station & Variants

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **APIs:** `POST /api/v1/sync/push` · `POST /api/v1/sync/pull`  
> **Audience:** Flutter Desktop / POS + Laravel  
> **Last Updated:** November 2026  

---

## 1. Problem (fixed on backend)

Desktop saved **Prepared Food** + variants (half/full/1kg) + add-ons, but after sync:

| Symptom on Web / other PC | Cause |
|---------------------------|--------|
| Product Type = General | `product_kind` not saved on push |
| Variants & Recipe tabs hidden | UI keys off `product_kind === prepared_food` |
| 0 variants | Nested `variants[]` ignored on push; not loaded on pull |

---

## 2. Fields Flutter must push on each product

Inside `payload.products[]`:

| Field | Example | Notes |
|-------|---------|--------|
| `client_uuid` | UUID | Required |
| `product_kind` | `prepared_food` | Also accepts `product_type`. Values: `prepared_food`, `packaged` / `packaged_product`, `raw_material`, `general` |
| `food_type` | `veg` / `non_veg` / `egg` / `vegan` / `na` | |
| `kitchen_station` | `Indian` \| null | |
| `prep_time_minutes` | `15` \| null | |
| `item_type` | `food` / `liquor` | Optional (KPI split) |
| `variants` | array | See below |
| `addons` | array | Optional separate list |

### Variants array (Desktop format)

```json
"variants": [
  { "local_id": 1, "client_uuid": "...", "size": "half", "color": "Variant", "price": 70, "barcode": "33535" },
  { "local_id": 2, "client_uuid": "...", "size": "full", "color": "Variant", "price": 100, "barcode": "33535" },
  { "local_id": 3, "client_uuid": "...", "size": "1 kg", "color": "Variant", "price": 250, "barcode": "33535" },
  { "local_id": 4, "client_uuid": "...", "size": "extra sev", "color": "Addon", "price": 20, "barcode": "ADDON-1" }
]
```

**Backend mapping:**

- `color = Variant` (or missing) → `product_variants` (`name` ← `size` or `name`)
- `color = Addon` → `product_addons` + pivot `product_addon_product`
- If `variants` present and `product_kind` missing/`general` → forced to **`prepared_food`**

Also accepted aliases: `name` instead of `size`; separate `addons: [{ name, price }]`.

---

## 3. Example push product

```json
{
  "branch_id": 1,
  "payload": {
    "products": [
      {
        "local_id": 29,
        "client_uuid": "a1b2c3d4-....",
        "name": "sev tamatar",
        "sku": "33535",
        "SKU": "33535",
        "price": 100,
        "product_kind": "prepared_food",
        "food_type": "na",
        "kitchen_station": null,
        "prep_time_minutes": null,
        "item_type": "food",
        "category_client_uuid": "...",
        "variants": [
          { "size": "half", "color": "Variant", "price": 70, "client_uuid": "v-1" },
          { "size": "full", "color": "Variant", "price": 100, "client_uuid": "v-2" },
          { "size": "1 kg", "color": "Variant", "price": 250, "client_uuid": "v-3" },
          { "size": "extra sev", "color": "Addon", "price": 20, "client_uuid": "a-1" }
        ]
      }
    ]
  }
}
```

### Mapping response (snippet)

```json
{
  "synced_mappings": {
    "products": [
      {
        "local_id": 29,
        "remote_id": 29,
        "client_uuid": "a1b2c3d4-....",
        "product_kind": "prepared_food",
        "food_type": "na",
        "variants": [
          { "remote_id": 101, "name": "half", "color": "Variant" },
          { "remote_id": 104, "name": "extra sev", "color": "Addon" }
        ]
      }
    ]
  }
}
```

---

## 4. Pull shape (multi-PC)

Each product includes:

```json
{
  "id": 29,
  "name": "sev tamatar",
  "SKU": "33535",
  "product_kind": "prepared_food",
  "product_type": "prepared_food",
  "food_type": "na",
  "kitchen_station": null,
  "prep_time_minutes": null,
  "item_type": "food",
  "category_id": 4,
  "variants": [
    {
      "id": 101,
      "remote_id": 101,
      "name": "half",
      "size": "half",
      "color": "Variant",
      "price": 70,
      "sku": "33535",
      "barcode": "33535"
    },
    {
      "id": 50,
      "remote_id": 50,
      "name": "extra sev",
      "size": "extra sev",
      "color": "Addon",
      "price": 20
    }
  ],
  "addons": [
    { "id": 50, "name": "extra sev", "color": "Addon", "price": 20 }
  ]
}
```

Desktop should:

1. Persist `product_kind` / `food_type` / kitchen / prep  
2. Upsert local variants by `remote_id` / `client_uuid` / `size`  
3. If `variants` or `addons` non-empty → keep UI type as **Prepared Food**

---

## 5. Deploy checklist

1. Deploy hotel code (`SyncController` + `ProductMasterService`)  
2. Run migration `2026_11_14_130000_add_client_uuid_to_product_variants` (optional but recommended)  
3. Push a prepared-food product with variants from Desktop  
4. Open same product on Web → Product Type = Prepared Food, variants visible  
5. Pull on second PC → same variants / add-ons  

---

## 6. Related guides

| File | Topic |
|------|--------|
| `FLUTTER_LICENSE_TENANT_SYNC_GUIDE.md` | License / tenant DB |
| `FLUTTER_PROFILE_AVATAR_SYNC_GUIDE.md` | Profile photo sync |
| `FLUTTER_DASHBOARD_METRICS_SYNC_GUIDE.md` | KPI metrics |
| `FLUTTER_SYNC_PAYMENT_ORDER_ID_GUIDE.md` | Payment ↔ order FK |
