# 🛠️ Backend Fix: `Unknown column 'amount' in 'payment_store'` (SQLSTATE[42S22])

---

## 1. Problem Summary / એરરની વિગત

જ્યારે ડેસ્કટોપ / ફ્લટર એપ્લિકેશન (Desktop / POS App) પરથી સેલ્સ કે પેમેન્ટ સિન્ક કરવા માટે સર્વર પર પુશ રિક્વેસ્ટ મોકલવામાં આવે છે (`POST https://hotel-erp.fableadtech.com/api/v1/sync/push`), ત્યારે નીચે મુજબનો **HTTP 500 Internal Server Error** આવતો હતો:

```json
{
  "status": "error",
  "message": "Sync push failed: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'amount' in 'INSERT INTO' (Connection: mysql, SQL: insert into `payment_store` (`order_id`, `amount`, `payment_method`, `status`, `branch_id`, `client_uuid`, `created_at`, `updated_at`) values (13, 0, cash, credit, 1, 70617900-0001-4000-8000-00000000000c, 2026-11-03 13:13:09, 2026-11-03 13:13:09))",
  "line": 829,
  "file": "/home/u573967329/domains/hotel-erp.fableadtech.com/public_html/vendor/laravel/framework/src/Illuminate/Database/Connection.php"
}
```

---

## 2. Root Cause Analysis / એરર શા માટે આવી?

1. **MySQL Table Schema Mismatch (`payment_amount` vs `amount`)**:
   - `payment_store` ટેબલમાં ઓરિજિનલ કોલમનું નામ **`payment_amount`** છે (`cash_amount`, `upi_amount`, `remaining_amount` સાથે).
   - જૂના `SyncController.php` માં સીધું જ `$payment->amount = $p['amount'] ?? 0;` સેટ કરવામાં આવતું હતું અને `$payment->payment_amount` સેટ જ થતું નહોતું.
   - પરિણામે MySQL ક્વેરીમાં `amount` કોલમ જતી હતી, જે ડેટાબેઝમાં ન હોવાથી `1054 Unknown column 'amount'` એરર આવતી હતી.

2. **Missing `branch_id` & `client_uuid` in DB Schema**:
   - સર્વરના `payment_store` ટેબલમાં `branch_id` કે `client_uuid` કોલમ ન હોય ત્યારે ક્વેરી ફેલ થતી હતી.

3. **Missing Required Fields in MySQL Strict Mode (`user_id`, `payment_date`, `payment_type`)**:
   - `payment_store` ટેબલમાં `user_id`, `payment_date`, અને `payment_type` કોલમ્સ `NOT NULL` છે. જૂના કંટ્રોલરમાં આ ફીલ્ડ્સ સેટ ન થતી હોવાથી strict mode માં પણ ક્વેરી ફેલ થવાનો ભય હતો.

4. **Zero Value Recorded (`values (13, 0, ...)`):**
   - પેલોડમાં `payment_amount` મોકલવામાં આવે ત્યારે કંટ્રોલર માત્ર `amount` કી ચેક કરતું હતું, જેથી રકમ `0` રેકોર્ડ થતી હતી.

---

## 3. Solution Implemented in Codebase / પ્રોજેક્ટમાં કરેલા સુધારા

અમે તમારા `hotel-erp-admin-product` પ્રોજેક્ટમાં નીચે મુજબ 3 મુખ્ય ફાઇલોમાં સંપૂર્ણ સોલ્યુશન લાગુ કરી દીધું છે:

### ફાઇલ 1: `app/Http/Controllers/api/SyncController.php`
- **Dynamic Schema Inspection**: હવે કંટ્રોલર `Schema::hasColumn('payment_store', 'amount')` અને `Schema::hasColumn('payment_store', 'branch_id')` ચેક કરીને જ એટ્રિબ્યુટ્સ સેટ કરે છે. જો ડેટાબેઝમાં કોલમ ન હોય તો પણ ક્વેરી ક્રેશ નહીં થાય!
- **Dual Amount Compatibility**: `$amount = floatval($p['payment_amount'] ?? $p['amount'] ?? 0.0);` — એપ્લિકેશન `amount` મોકલે કે `payment_amount` મોકલે, બંને કેસમાં સાચી રકમ પકડાશે.
- **Auto User Resolution**: ઓર્ડરમાંથી અથવા બ્રાન્ચમાંથી `user_id` આપોઆપ મેળવી લેશે.
- **Method & Split Calculations**: `cash_amount` અને `upi_amount` પદ્ધતિ મુજબ આપોઆપ યોગ્ય રીતે સેટ થશે.
- **Order Synchronization**: પેમેન્ટ આવતા જ સંબંધિત ઓર્ડરનું `remaining_amount` અને `payment_status` (completed / partially) આપોઆપ અપડેટ થઈ જશે.
- **Safe Pull Support**: પુલ API માં `payment_store` સાથે `order:id,client_uuid` eager load થશે.

### ફાઇલ 2: `app/Models/PaymentStore.php`
- `$fillable` એરેમાં `client_uuid`, `branch_id`, અને `amount` ઉમેર્યા.
- `$appends` એરેમાં `amount` અને `order_client_uuid` ઉમેર્યા.
- `getAmountAttribute()` અને `getOrderClientUuidAttribute()` એક્સેસર બનાવ્યા, જેથી ડેસ્કટોપ ડ્રિફ્ટ DB જ્યારે પુલ કરે ત્યારે તેને સીધું જ `amount` અને `order_client_uuid` મળી જાય.

### ફાઇલ 3: `database/migrations/2026_11_03_150000_add_amount_and_branch_id_to_payment_store_table.php`
- નવું માઇગ્રેશન બનાવ્યું જે સુરક્ષિત રીતે `amount`, `branch_id`, અને `client_uuid` કોલમ્સ જો ન હોય તો ઉમેરે છે અને જૂના રેકોર્ડ્સમાં `amount = payment_amount` બેકફિલ કરે છે.

---

## 4. Live Server Deployment Steps / લાઈવ સર્વર પર શું કરવું?

લાઈવ સર્વર (`hotel-erp.fableadtech.com`) પર આ ફિક્સ લાગુ કરવા માટે નીચેના સ્ટેપ્સ અનુસરો:

### સ્ટેપ 1: ફાઇલો અપલોડ કરો (Git Pull અથવા cPanel File Manager)
નીચેની 3 ફાઇલો લાઈવ સર્વર પર અપડેટ કરો:
1. `app/Http/Controllers/api/SyncController.php`
2. `app/Models/PaymentStore.php`
3. `database/migrations/2026_11_03_150000_add_amount_and_branch_id_to_payment_store_table.php`

### સ્ટેપ 2: ડેટાબેઝ અપડેટ કરો

#### વિકલ્પ A: phpMyAdmin દ્વારા (સૌથી સરળ - Best Option)
તમારા હોસ્ટિંગના **phpMyAdmin** માં જાઓ, હોટેલ ERP ના ડેટાબેઝને સિલેક્ટ કરો અને **SQL** ટેબમાં નીચેની ક્વેરી રન કરો:

```sql
-- 1. payment_store ટેબલમાં amount કોલમ ઉમેરો
ALTER TABLE `payment_store` 
ADD COLUMN IF NOT EXISTS `amount` DECIMAL(12,2) NULL DEFAULT 0.00 AFTER `payment_amount`;

-- 2. payment_store ટેબલમાં branch_id કોલમ ઉમેરો
ALTER TABLE `payment_store` 
ADD COLUMN IF NOT EXISTS `branch_id` BIGINT UNSIGNED NULL DEFAULT 1 AFTER `status`;

-- 3. payment_store ટેબલમાં client_uuid કોલમ ઉમેરો (જો ન હોય તો)
ALTER TABLE `payment_store` 
ADD COLUMN IF NOT EXISTS `client_uuid` VARCHAR(100) NULL AFTER `id`;

-- 4. client_uuid માટે ઇન્ડેક્સ બનાવો
ALTER TABLE `payment_store` 
ADD INDEX IF NOT EXISTS `idx_payment_store_client_uuid` (`client_uuid`);

-- 5. જૂના રેકોર્ડ્સમાં amount ને payment_amount સાથે સિન્ક કરો
UPDATE `payment_store` 
SET `amount` = `payment_amount` 
WHERE (`amount` IS NULL OR `amount` = 0.00) AND `payment_amount` > 0;
```

#### વિકલ્પ B: SSH Terminal દ્વારા (જો SSH એક્સેસ હોય તો)
```bash
cd /home/u573967329/domains/hotel-erp.fableadtech.com/public_html
php artisan migrate --force
```

### સ્ટેપ 3: Laravel Cache ક્લીયર કરો
SSH અથવા cPanel Terminal પર રન કરો:
```bash
php artisan config:clear
php artisan route:clear
```

---

## 5. Verification & Test Results / લોકલ ટેસ્ટિંગના પરિણામો

આ ફિક્સ લોકલ એન્વાયર્નમેન્ટમાં સંપૂર્ણ ટેસ્ટ કરવામાં આવ્યું છે:
- **Test 1**: `amount: 500.00` સાથે પુશ રિક્વેસ્ટ મોકલી -> **HTTP 200 OK**
- **Test 2**: `payment_amount: 750.25` સાથે પુશ રિક્વેસ્ટ મોકલી -> **HTTP 200 OK**
- **Test 3**: ઓર્ડર સાથે લિંક કરેલ પેમેન્ટ સિન્ક કર્યું -> ઓર્ડરનું `remaining_amount` આપોઆપ `0.00` અને `payment_status` આપોઆપ `completed` થઈ ગયું -> **HTTP 200 OK**

```json
{
  "status": "success",
  "message": "Data synchronized successfully.",
  "server_time": "2026-11-03 15:04:35",
  "synced_mappings": {
    "payment_store": [
      {
        "local_id": 12,
        "remote_id": 2,
        "client_uuid": "476ac991-8091-4d48-b6b8-40cc68b76b28"
      }
    ]
  }
}
```

---

## 6. Summary Checklist / ચેકલિસ્ટ

| ક્રમ | કાર્ય | સ્થિતિ |
|---|---|:---:|
| 1 | `SyncController.php` માં Dynamic Schema Check અને Dual Amount Mapping ઉમેર્યું | ✅ પૂર્ણ |
| 2 | `PaymentStore.php` મોડેલમાં `$fillable`, `$appends`, અને એક્સેસર્સ ઉમેર્યા | ✅ પૂર્ણ |
| 3 | નવી માઇગ્રેશન ફાઇલ `2026_11_03_150000_...` બનાવી | ✅ પૂર્ણ |
| 4 | લોકલ ડેટાબેઝ પર માઇગ્રેશન અને મોક સિન્ક પુશ સફળતાપૂર્વક ટેસ્ટ કર્યા | ✅ પૂર્ણ (HTTP 200) |
| 5 | લાઈવ સર્વર phpMyAdmin માટે SQL ક્વેરીઝ તૈયાર કરી | ✅ પૂર્ણ |

