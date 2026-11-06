# Category Apply GST Sync Guide (Pull & Push API)

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **Module:** Menu & Inventory / Category Management  
> **Feature:** Category-Level "Apply GST" Toggle (`gst_applicable`)  
> **Target Clients:** Web Admin, Flutter POS Desktop & Mobile App  
> **Last Updated:** November 2026  

---

## 1. Overview & Business Logic

The **Apply GST** feature allows hotel/restaurant administrators to control whether GST (Goods and Services Tax) applies to items in a specific food or beverage category.

### Use Case
- **GST Applicable (`On` / `true` / `1`)**: Standard restaurant menu items (e.g., Punjabi dishes, Mocktails, Desserts, Chinese cuisine) where GST must be calculated during billing.
- **GST Exempt (`Off` / `false` / `0`)**: Specific categories (e.g., Cold Drinks with MRP inclusive of all taxes, pure milk items, or GST-exempt groceries) where billing must **not** charge GST, even when order billing is set to "With GST".

### POS Billing Calculation Rule
When creating or updating an order/invoice:
1. Check Order level: Is bill "With GST"?
2. Check Product category: Look up `category.gst_applicable`.
3. If `category.gst_applicable == false` (or `0`), the tax percentage for this item is **0%** (GST is omitted).
4. If `category.gst_applicable == true` (or `1`), the item receives the standard tax rate configured for the product/restaurant.

---

## 2. Database Schema

The `categories` table includes the column `gst_applicable`:

```sql
ALTER TABLE `categories` 
ADD COLUMN `gst_applicable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `name`;
```

- **Migration File:** `database/migrations/2026_11_05_193000_add_gst_applicable_to_categories.php`
- **Default Value:** `1` (`true`) so all existing categories preserve GST billing until explicitly turned off.

### Mapped Laravel Model (`App\Models\Category.php`)
```php
protected $fillable = [
    'name',
    'gst_applicable',
    'image',
    'branch_id',
    'isDeleted',
    'client_uuid',
];

protected $casts = [
    'gst_applicable' => 'boolean',
    'isDeleted' => 'integer',
];

public function appliesGst(): bool
{
    if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'gst_applicable')) {
        return true;
    }

    return $this->gst_applicable !== false;
}
```

---

## 3. Flutter Drift Schema (SQLite)

When using Drift for offline local database storage in the Flutter Desktop or Mobile app, define the `CategoriesTable` as follows:

```dart
import 'package:drift/drift.dart';

mixin SyncColumns on Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().withLength(min: 36, max: 36)();
  IntColumn get remoteId => integer().nullable()();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  IntColumn get isDeleted => integer().withDefault(const Constant(0))();
  DateTimeColumn get createdAt => dateTime().nullable()();
  DateTimeColumn get updatedAt => dateTime().nullable()();
}

class CategoriesTable extends Table with SyncColumns {
  TextColumn get name => text().withLength(min: 1, max: 150)();
  TextColumn get image => text().nullable()();
  BoolColumn get gstApplicable => boolean().withDefault(const Constant(true))();
  IntColumn get branchId => integer().nullable()();
}
```

---

## 4. Delta Pull API (Server -> Flutter / Client)

Downloads all categories (or only categories modified since `last_sync_timestamp`).

- **Endpoint:** `POST /api/v1/sync/pull`
- **Headers:**
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  Content-Type: application/json
  ```

### Request Payload:
```json
{
  "branch_id": 1,
  "last_sync_timestamp": "2026-11-05 10:00:00",
  "tables": ["categories"]
}
```
*(Note: If `"tables"` is omitted or empty, all 53 ERP tables are pulled).*

### Response (200 OK):
```json
{
  "status": "success",
  "server_time": "2026-11-06 15:00:00",
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Chinese",
        "gst_applicable": false,
        "image": "img/category/chinese.png",
        "image_url": "https://hotel-erp.fableadtech.com/storage/img/category/chinese.png",
        "branch_id": 1,
        "client_uuid": "f2198267-3103-403b-abaa-6863492ac4f3",
        "isDeleted": 0,
        "created_at": "2026-10-29T11:00:00.000000Z",
        "updated_at": "2026-11-06T14:30:00.000000Z"
      },
      {
        "id": 2,
        "name": "Punjabi",
        "gst_applicable": true,
        "image": "img/category/punjabi.jpg",
        "image_url": "https://hotel-erp.fableadtech.com/storage/img/category/punjabi.jpg",
        "branch_id": 1,
        "client_uuid": "73be5a2d-80af-4a3c-8984-1af8e18e4110",
        "isDeleted": 0,
        "created_at": "2026-10-26T15:24:09.000000Z",
        "updated_at": "2026-11-06T14:35:00.000000Z"
      }
    ],
    "deleted_ids": {},
    "deleted_records": {}
  }
}
```

### Flutter Drift Upsert Logic (Pull):
```dart
Future<void> upsertCategoryFromPull(Map<String, dynamic> json) async {
  final clientUuid = json['client_uuid'] as String;
  final gstApplicable = json['gst_applicable'] == true || json['gst_applicable'] == 1;

  final existing = await (select(categoriesTable)..where((tbl) => tbl.clientUuid.equals(clientUuid))).getSingleOrNull();

  if (existing == null) {
    await into(categoriesTable).insert(CategoriesTableCompanion.insert(
      clientUuid: clientUuid,
      remoteId: Value(json['id'] as int?),
      name: json['name'] as String,
      image: Value(json['image'] as String?),
      gstApplicable: Value(gstApplicable),
      branchId: Value(json['branch_id'] as int?),
      isDeleted: Value((json['isDeleted'] as int?) ?? 0),
      isDirty: const Value(false),
      createdAt: Value(json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null),
      updatedAt: Value(json['updated_at'] != null ? DateTime.tryParse(json['updated_at']) : null),
    ));
  } else {
    await (update(categoriesTable)..where((tbl) => tbl.clientUuid.equals(clientUuid))).write(
      CategoriesTableCompanion(
        remoteId: Value(json['id'] as int?),
        name: Value(json['name'] as String),
        image: Value(json['image'] as String?),
        gstApplicable: Value(gstApplicable),
        branchId: Value(json['branch_id'] as int?),
        isDeleted: Value((json['isDeleted'] as int?) ?? 0),
        isDirty: const Value(false),
        updatedAt: Value(json['updated_at'] != null ? DateTime.tryParse(json['updated_at']) : null),
      ),
    );
  }
}
```

---

## 5. Batch Push API (Flutter / Client -> Server)

Uploads locally created or modified categories (e.g. cashier changed GST switch offline or added category).

- **Endpoint:** `POST /api/v1/sync/push`
- **Headers:**
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  Content-Type: application/json
  ```

### Request Payload:
```json
{
  "branch_id": 1,
  "payload": {
    "categories": [
      {
        "local_id": 12,
        "client_uuid": "f2198267-3103-403b-abaa-6863492ac4f3",
        "name": "Chinese",
        "gst_applicable": false,
        "branch_id": 1,
        "isDeleted": 0
      },
      {
        "local_id": 13,
        "client_uuid": "8e87d8c0-176b-487d-9565-821b9cc989f9",
        "name": "Cold Drinks",
        "gst_applicable": true,
        "branch_id": 1,
        "isDeleted": 0
      }
    ]
  }
}
```

### Response (200 OK):
```json
{
  "status": "success",
  "message": "Batch push synchronized successfully.",
  "server_time": "2026-11-06 15:05:00",
  "synced": {
    "categories": [
      {
        "local_id": 12,
        "remote_id": 1,
        "client_uuid": "f2198267-3103-403b-abaa-6863492ac4f3"
      },
      {
        "local_id": 13,
        "remote_id": 6,
        "client_uuid": "8e87d8c0-176b-487d-9565-821b9cc989f9"
      }
    ]
  }
}
```


## 6. Git Pull and Push Commands

To pull latest code from GitHub and push your local branch changes:

```bash
# 1. Navigate to the project directory
cd e:\xampp8.1\htdocs\ERP\hotel-erp-admin-product

# 2. Check current status
git status

# 3. Fetch latest references from remote
git fetch origin

# 4. Pull latest changes with rebase (preserves your local commits)
git pull --rebase origin dev-anjali

# 5. Stage modified sync files
git add app/Http/Controllers/api/SyncController.php
git add FLUTTER_DRIFT_SYNC_GUIDE.md
git add CATEGORY_APPLY_GST_SYNC_GUIDE.md

# 6. Commit the changes
git commit -m "sync: add gst_applicable support to category sync pull and push"

# 7. Push commits to GitHub repository
git push origin dev-anjali
```

