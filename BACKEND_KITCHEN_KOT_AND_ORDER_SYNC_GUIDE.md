# 🍳 Backend & KOT Sync Guide: Order Items & Kitchen Tickets

---

## 1. Problem Summary / એરરની વિગત

જ્યારે ડેસ્કટોપ / ફ્લટર એપ્લિકેશન (Desktop / POS App) પરથી Kitchen Order Tickets (KOT) કે નવા ઓર્ડર આઇટમ્સ સાથે સર્વર પર પુશ રિક્વેસ્ટ મોકલવામાં આવે છે (`POST https://hotel-erp.fableadtech.com/api/v1/sync/push`), ત્યારે નીચે મુજબનો **HTTP 500 Internal Server Error** આવતો હતો:

```json
{
  "status": "error",
  "message": "Sync push failed: SQLSTATE[HY000]: General error: 1364 Field 'user_id' doesn't have a default value (Connection: mysql, SQL: insert into `order_items` (`order_id`, `product_id`, `quantity`, `price`, `total_amount`, `category_id`, `quantity_type_id`, `branch_id`, `client_uuid`, `created_at`, `updated_at`) values (19, 12, 1, 250, 0, 2, 1, 1, '6974656d-0001-4000-8000-000000000028', '2026-11-03 16:58:33', '2026-11-03 16:58:33'))",
  "line": 829,
  "file": "/home/u573967329/domains/hotel-erp.fableadtech.com/public_html/vendor/laravel/framework/src/Illuminate/Database/Connection.php"
}
```

---

## 2. Root Cause Analysis / એરર શા માટે આવી?

1. **Missing `user_id` in `order_items`**:
   - `order_items` ટેબલમાં `user_id` કોલમ `NOT NULL` છે અને તેની કોઈ Default value નથી.
   - જૂના `SyncController.php` માં આઇટમ ઉમેરતી વખતે `$orderItem->user_id` સેટ જ નહોતું થતું. જેથી MySQL Strict Mode માં `1364 Field 'user_id' doesn't have a default value` એરર આવીને આખો સિન્ક ટ્રાન્ઝેક્શન રોલબેક થઈ જતો હતો.

2. **Table No Type Mismatch (`1366 Incorrect integer value: 'Table 5'`)**:
   - ડેટાબેઝના `orders` ટેબલમાં `table_no` કોલમનો પ્રકાર `BIGINT UNSIGNED` છે.
   - જ્યારે એપ પરથી ટેબલનું નામ સ્ટ્રિંગ તરીકે (દા.ત. `'Table 5'`, `'T-1'`) આવે, ત્યારે MySQL ઇન્ટીજરમાં કન્વર્ટ ન કરી શકવાને કારણે ક્રેશ થતું હતું.

3. **KOT Delivery Status ENUM Warning (`1265 Data truncated for column 'delivery_status'`)**:
   - `orders` ટેબલનું `delivery_status` માત્ર `enum('pending','delivered','partially_delivered','cancelled')` હતું.
   - કિચન KOT માં જ્યારે નવી સ્થિતિઓ જેવી કે `'running'`, `'ready'`, `'served'` આવે, ત્યારે MySQL ENUM માં મેચ ન થવાને કારણે ડેટા ટ્રન્કેશન એરર ફેંકતું હતું.

4. **Missing Category ID Fallback**:
   - `order_items.category_id` કોલમ પણ `NOT NULL` છે. જો એપમાંથી ક્યારેય category_id ન મોકલાય તો તે પણ ફેલ થતું હતું.

---

## 3. Solution Implemented in Codebase / પ્રોજેક્ટમાં કરેલા સુધારા

અમે તમારા `hotel-erp-admin-product` પ્રોજેક્ટમાં તમામ સમસ્યાઓનું પરમેનન્ટ સોલ્યુશન લાગુ કરી દીધું છે:

### ફાઇલ 1: `app/Http/Controllers/api/SyncController.php`
- **Safe `user_id` Resolution**:
  ```php
  $itemUserId = $it['user_id'] 
      ?? $it['customer_id'] 
      ?? $order->user_id 
      ?? $userId 
      ?? 1;
  $orderItem->user_id = $itemUserId;
  ```
  હવે આઇટમ પેલોડ, પેરન્ટ ઓર્ડર, કે ઓથેન્ટિકેટેડ યુઝરમાંથી આપોઆપ `user_id` સેટ થાય છે.
- **Smart `table_no` Parsing**:
  જો `'Table 5'` આવે તો તેમાંથી ટેબલ માસ્ટર લુકઅપ કરીને અથવા ડિજિટ્સ એક્સટ્રેક્ટ કરીને સુરક્ષિત રીતે `5` સેટ કરશે, જેથી MySQL ક્રેશ ન થાય.
- **Auto Category Resolution**:
  જો આઇટમમાં category_id ન હોય, તો પ્રોડક્ટમાંથી `prd->category_id` આપોઆપ મેળવી લેશે.
- **Non-Destructive Item Upsert**:
  જૂના બધા રેકોર્ડ ડિલીટ કરવાને બદલે `firstOrNew(['client_uuid' => $itemUuid])` વાપરે છે, જેથી ક્લાયન્ટના રિમોટ IDs જળવાઈ રહે.
- **Standalone `payload['order_items']` Support**:
  જો ઓર્ડર સિવાય સીધું જ `order_items` એરે પુશ કરવામાં આવે તો તે પણ હેન્ડલ થશે.
- **Eager Loading in Pull**:
  `/api/sync/pull` કરતી વખતે `order_items` સાથે પ્રોડક્ટ અને ઓર્ડરની વિગતો આપોઆપ લોડ થશે.

### ફાઇલ 2: `app/Models/OrderItem.php`
- `$fillable` માં `'client_uuid'` ઉમેર્યું.
- `$appends` માં `'order_client_uuid'` અને `'product_client_uuid'` ઉમેર્યા જેથી Flutter Drift DB સીધા જ આ ફીલ્ડ્સ લિંક કરી શકે.

### ફાઇલ 3: `app/Models/Order.php`
- `$fillable` માં `'client_uuid'` ઉમેર્યું.

### ફાઇલ 4: `database/migrations/2026_11_03_173000_make_user_id_and_category_id_nullable_in_order_items_table.php`
- ડેટાબેઝ લેવલે `order_items.user_id` અને `order_items.category_id` ને `NULLABLE` બનાવ્યા.
- `orders.delivery_status` ને `VARCHAR(50)` માં કન્વર્ટ કર્યું જેથી `'running'`, `'ready'`, `'served'`, `'delivered'` બધી કિચન સ્ટેટસ સપોર્ટ થાય.

---

## 4. Live Server Deployment Steps / લાઈવ સર્વર પર શું કરવું?

લાઈવ સર્વર (`hotel-erp.fableadtech.com`) પર આ ફિક્સ લાગુ કરવા માટે:

### પદ્ધતિ A: cPanel Terminal અથવા SSH દ્વારા (સૌથી સરળ - Recommended)

ટર્મિનલમાં નીચેના કમાન્ડ્સ **એક પછી એક** રન કરો:

```bash
cd /home/u573967329/domains/hotel-erp.fableadtech.com/public_html
```

```bash
git pull origin main
```

```bash
php artisan migrate --force
```

```bash
php artisan config:clear
```

```bash
php artisan route:clear
```

---

### પદ્ધતિ B: phpMyAdmin દ્વારા (Direct SQL Queries)

જો તમારી પાસે ટર્મિનલ ન હોય અને phpMyAdmin વાપરવું હોય, તો phpMyAdmin માં ડેટાબેઝ સિલેક્ટ કરી **SQL** ટેબમાં નીચેની ક્વેરી રન કરો:

```sql
-- 1. order_items ટેબલમાં user_id ને NULLABLE બનાવો
ALTER TABLE `order_items` MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL;

-- 2. order_items ટેબલમાં category_id ને NULLABLE બનાવો
ALTER TABLE `order_items` MODIFY COLUMN `category_id` BIGINT UNSIGNED NULL DEFAULT NULL;

-- 3. orders ટેબલમાં delivery_status ને VARCHAR(50) બનાવો જેથી KOT kitchen statuses સપોર્ટ થાય
ALTER TABLE `orders` MODIFY COLUMN `delivery_status` VARCHAR(50) NULL DEFAULT 'pending';

-- 4. order_items માં client_uuid ઇન્ડેક્સ સુનિશ્ચિત કરો (જો ન હોય તો)
ALTER TABLE `order_items` ADD COLUMN IF NOT EXISTS `client_uuid` VARCHAR(100) NULL AFTER `id`;
```

---

## 5. Kitchen KOT Lifecycle & Sync Overview

```
┌─────────────────────────────────┐
│ Dine-In Tables / POS Terminal   │
│ Order Placed with Items (KOT)   │
└────────────────┬────────────────┘
                 │
                 ▼
     [KOT Status: "running"]
     (Chef receives ticket in Kitchen, starts preparing)
                 │
                 │ Chef clicks "Mark Ready" in Kitchen Board
                 ▼
     [KOT Status: "ready"]
     (Food is ready, Captain / Waiter notified for pickup)
                 │
                 │ Waiter serves food to table, clicks "Mark Served"
                 ▼
     [KOT Status: "served"]
     (Food served to guest)
                 │
                 │ Guest asks for bill & checkout
                 ▼
     [Payment Status: "paid" / Checkout Completed]
```

### KOT Fields Mapping:
| Field | Value Examples | Description |
|---|---|---|
| `delivery_status` | `'running'`, `'ready'`, `'served'`, `'delivered'` | રિયલ-ટાઇમ કિચન પ્રોગ્રેસ સ્ટેટસ |
| `order_type` | `'Dine-In'`, `'Takeaway'`, `'Delivery'` | ઓર્ડરનો પ્રકાર |
| `table_no` | `1`, `2`, `5`, `12` | ડાઇનિંગ ટેબલ નંબર |
| `table_status` | `1` (Occupied) / `0` (Vacant) | ટેબલ ઓક્યુપન્સી સ્ટેટસ |
| `payment_status` | `'pending'` (Running bill), `'completed'` (Settled) | બિલિંગ સ્ટેટસ |

---

## 6. Verification & Test Results / ટેસ્ટિંગ પરિણામો

લોકલ એન્વાયર્નમેન્ટમાં KOT ઓર્ડર અને આઇટમ્સ પુશ કરીને સંપૂર્ણ ટેસ્ટિંગ કરવામાં આવ્યું:
- **Test Payload**: Order with Table No `'Table 5'`, Kitchen status `'running'`, આઇટમમાં `user_id` આપ્યા વગર.
- **પરિણામ**: **HTTP 200 OK**
```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "server_time": "2026-11-03 17:40:57",
  "synced_mappings": {
    "orders": [
      {
        "local_id": 101,
        "remote_id": 3,
        "client_uuid": "1d0db22c-8201-46dd-a1d0-530a10686568",
        "items": [
          {
            "local_id": 501,
            "remote_id": 2,
            "client_uuid": "774990a1-68cc-40c3-8a32-38c7063b1049"
          }
        ]
      }
    ],
    "order_items": [
      {
        "local_id": 501,
        "remote_id": 2,
        "client_uuid": "774990a1-68cc-40c3-8a32-38c7063b1049"
      }
    ]
  }
}
```
ડેટાબેઝમાં `order_items` માં `user_id = 1`, `category_id = 1`, `table_no = 5`, `delivery_status = 'running'` કોઈપણ એરર વગર સફળતાપૂર્વક સ્ટોર થાય છે!

