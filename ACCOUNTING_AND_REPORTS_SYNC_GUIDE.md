# 📊 Hotel ERP: Accounting, Ledgers & Reports Sync Guide

> **Project:** Hotel ERP (`hotel-erp-admin-product`)  
> **Target Client:** Flutter Desktop Application (Windows / macOS / Linux) & Mobile POS  
> **Local Database:** Drift (SQLite)  
> **Backend Architecture:** Laravel Multi-Tenant ERP with Sync Engine (`/api/v1/sync/*`) & Dedicated Accounting APIs  
> **Scope:** 13 Specific Modules: Account Ledger, Income Statement, Banks, All Expenses, New Expenses, All Expense Type, Bank Book, Cash Book, GST Purchase Report, GST Sales Report, Purchase Report, Expense Report, Orders Report.  
> **Updated:** November 2026  

---

## Table of Contents
1. [Architecture Overview & Sync Strategy](#1-architecture-overview--sync-strategy)
2. [Module Coverage Matrix (13 Modules)](#2-module-coverage-matrix-13-modules)
3. [Base Synced Database Tables (Drift SQLite Definitions)](#3-base-synced-database-tables-drift-sqlite-definitions)
   - [3.1. Banks (`bank_master`)](#31-banks-bank_master)
   - [3.2. Expense Types (`expense_types`)](#32-expense-types-expense_types)
   - [3.3. Expenses (`expenses`)](#33-expenses-expenses)
   - [3.4. Payment Store (`payment_store`)](#34-payment-store-payment_store)
   - [3.5. Orders & Order Items (`orders`, `order_items`)](#35-orders--order-items-orders-order_items)
   - [3.6. Purchases & Invoices (`purchases`, `purchase_invoice`)](#36-purchases--invoices-purchases-purchase_invoice)
4. [Two-Way Sync Engine: Pull & Push Payloads](#4-two-way-sync-engine-pull--push-payloads)
   - [4.1. Delta Pull Payload (`POST /api/v1/sync/pull`)](#41-delta-pull-payload-post-apiv1syncpull)
   - [4.2. Batch Push Payload (`POST /api/v1/sync/push`)](#42-batch-push-payload-post-apiv1syncpush)
5. [Offline Drift SQL Computation & Ledgers](#5-offline-drift-sql-computation--ledgers)
   - [5.1. Module 1: Account Ledger (Customer & Vendor Running Balances)](#51-module-1-account-ledger-customer--vendor-running-balances)
   - [5.2. Module 2: Income Statement (Profit & Loss Statement)](#52-module-2-income-statement-profit--loss-statement)
   - [5.3. Module 3: Bank Book (Digital & Bank Ledgers)](#53-module-3-bank-book-digital--bank-ledgers)
   - [5.4. Module 4: Cash Book (Daily Cash Inflows & Outflows)](#54-module-4-cash-book-daily-cash-inflows--outflows)
6. [Offline Drift SQL Computation for Reports](#6-offline-drift-sql-computation-for-reports)
   - [6.1. Module 5: GST Purchase Report (GSTR-2 ITC Reconciliation)](#61-module-5-gst-purchase-report-gstr-2-itc-reconciliation)
   - [6.2. Module 6: GST Sales Report (GSTR-1 Outward Supplies)](#62-module-6-gst-sales-report-gstr-1-outward-supplies)
   - [6.3. Module 7: Purchase Report](#63-module-7-purchase-report)
   - [6.4. Module 8: Expense Report](#64-module-8-expense-report)
   - [6.5. Module 9: Orders Report (POS & Dining Sales)](#65-module-9-orders-report-pos--dining-sales)
7. [Dedicated Server REST APIs & Export Endpoints](#7-dedicated-server-rest-apis--export-endpoints)
8. [Edge Cases, Split Payments & Conflict Resolution](#8-edge-cases-split-payments--conflict-resolution)

---

## 1. Architecture Overview & Sync Strategy

The 13 modules fall into two operational categories:

1. **Transactional & Master Records (Direct 2-Way Sync):**
   - **Banks** (`bank_master`)
   - **All Expense Types** (`expense_types`)
   - **All Expenses / New Expenses** (`expenses`)
   - **Orders & Payments** (`orders`, `order_items`, `payment_store`)
   - **Purchases & Invoices** (`purchases`, `purchase_invoice`)
   - *Strategy:* Stored in the local Drift SQLite database with `client_uuid`, `is_dirty`, and `is_deleted` flags. Synced with the central server via Delta Pull and Batch Push.

2. **Derived / Aggregated Ledgers & Reports (Computed On-The-Fly):**
   - **Account Ledger**, **Income Statement**, **Bank Book**, **Cash Book**
   - **GST Purchase Report**, **GST Sales Report**, **Purchase Report**, **Expense Report**, **Orders Report**
   - *Strategy:* Reports and financial ledgers **are not static database tables**. Instead, they are generated via local SQL queries and aggregation pipelines directly from synced Drift SQLite base tables when offline, or fetched via direct Laravel REST APIs when online.

```mermaid
flowchart TD
    subgraph Local [Flutter Desktop / POS Offline App]
        DriftDB[(Drift SQLite Database)]
        
        subgraph SyncedTables [Raw Base Tables (Two-Way Synced)]
            T_Bank[bank_master]
            T_ExpType[expense_types]
            T_Exp[expenses]
            T_Pay[payment_store]
            T_Ord[orders & order_items]
            T_Pur[purchases & invoices]
        end
        
        subgraph Engine [Drift Query & Aggregation Engine]
            Calc_Ledger[Account Ledger Calculation]
            Calc_PL[Income Statement / P&L Calculation]
            Calc_Books[Cash Book & Bank Book Balances]
            Calc_GST[GST R1 & R2 Calculations]
            Calc_Rep[Orders, Purchase & Expense Reports]
        end
        
        UI[Desktop UI Screens & Print Preview]
    end

    subgraph Remote [Laravel Multi-Tenant Server]
        API_Sync[POST /api/v1/sync/pull<br/>POST /api/v1/sync/push]
        API_Reports[Direct Reporting & PDF/Excel APIs]
        MySQL[(Tenant MySQL DB)]
    end

    SyncedTables <-->|Delta Sync| API_Sync
    API_Sync <--> MySQL
    SyncedTables --> Engine
    Engine --> UI
    UI -.->|Direct Online Fetch / PDF Export| API_Reports
    API_Reports <--> MySQL
```

---

## 2. Module Coverage Matrix (13 Modules)

| # | Requested Module | Type | Underlying DB Tables | Primary Controller | Sync Direction |
|---|---|---|---|---|---|
| **1** | **Account Ledger** | Computed Ledger | `orders`, `purchases`, `purchase_invoice`, `payment_store`, `users` | `api\AccountLedgerController` | Computed locally or fetched via API |
| **2** | **Income Statement** | Computed Statement | `orders`, `purchase_invoice`, `payment_store`, `expenses` | `api\IncomeSheetController` | Computed locally or fetched via API |
| **3** | **Banks** | Master Data | `bank_master` | `api\BankMasterController` | Two-Way Sync (`pull` & `push`) |
| **4** | **All Expenses** | Transactional Data | `expenses`, `expense_types`, `bank_master` | `api\ExpenseController` | Two-Way Sync (`pull` & `push`) |
| **5** | **New Expenses** | Transactional Action | `expenses` | `api\ExpenseController` | Push with `client_uuid` |
| **6** | **All Expense Type** | Master Data | `expense_types` | `api\ExpenseTypeController` | Two-Way Sync (`pull` & `push`) |
| **7** | **Bank Book** | Computed Ledger | `payment_store`, `expenses`, `bank_master` | `api\TransactionApiController` | Computed locally or fetched via API |
| **8** | **Cash Book** | Computed Ledger | `payment_store`, `expenses` | `api\TransactionController` | Computed locally or fetched via API |
| **9** | **GST Purchase Report** | Tax Report (GSTR-2) | `purchases`, `purchase_invoice`, `users`, `user_details` | `api\GstController` | Computed locally or fetched via API |
| **10** | **GST Sales Report** | Tax Report (GSTR-1) | `orders`, `order_items`, `taxes`, `users`, `user_details` | `api\GstController` | Computed locally or fetched via API |
| **11** | **Purchase Report** | Operational Report | `purchases`, `purchase_invoice`, `users` | `api\PurchaseController` | Computed locally or fetched via API |
| **12** | **Expense Report** | Operational Report | `expenses`, `expense_types` | `api\ExpenseController` | Computed locally or fetched via API |
| **13** | **Orders Report** | POS / Dining Report | `orders`, `order_items`, `users`, `table_no`, `payment_store` | `api\SalesController` | Computed locally or fetched via API |

---

## 3. Base Synced Database Tables (Drift SQLite Definitions)

Add these table definitions into your Flutter app's Drift database schema (`app_database.dart`). Every table inherits synchronization tracking fields: `client_uuid`, `remote_id`, `is_dirty`, and `is_deleted`.

### 3.1. Banks (`bank_master`)

```dart
import 'package:drift/drift.dart';

@DataClassName('BankMasterEntry')
class BankMasterTable extends Table {
  @override
  String get tableName => 'bank_master';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  TextColumn get bankName => text()();
  TextColumn get accountNumber => text().nullable()();
  TextColumn get ifscCode => text().nullable()();
  TextColumn get branchName => text().nullable()();
  RealColumn get openingBalance => real().withDefault(const Constant(0.0))();
  RealColumn get currentBalance => real().withDefault(const Constant(0.0))();
  TextColumn get status => text().withDefault(const Constant('active'))(); // 'active', 'inactive'
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

### 3.2. Expense Types (`expense_types`)

```dart
@DataClassName('ExpenseTypeEntry')
class ExpenseTypesTable extends Table {
  @override
  String get tableName => 'expense_types';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  TextColumn get type => text()(); // e.g. "Vegetables", "Utilities", "Salaries", "Rent"
  IntColumn get createdBy => integer().nullable()();
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

### 3.3. Expenses (`expenses`)

```dart
@DataClassName('ExpenseEntry')
class ExpensesTable extends Table {
  @override
  String get tableName => 'expenses';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  TextColumn get srNo => text().nullable()();
  TextColumn get expenseName => text()();
  DateTimeColumn get expenseDate => dateTime()();
  RealColumn get amount => real().withDefault(const Constant(0.0))();
  TextColumn get description => text().nullable()();
  
  IntColumn get expenseTypeId => integer().nullable()();
  TextColumn get expenseTypeClientUuid => text().nullable()();
  
  TextColumn get paymentMode => text().withDefault(const Constant('Cash'))(); // 'Cash', 'Bank'
  IntColumn get bankId => integer().nullable()();
  TextColumn get bankClientUuid => text().nullable()();
  
  IntColumn get createdBy => integer().nullable()();
  TextColumn get receiptImage => text().nullable()();
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

### 3.4. Payment Store (`payment_store`)

`payment_store` is the central ledger that binds sales orders, purchases, custom invoices, cash receipts, and bank credits/debits.

```dart
@DataClassName('PaymentStoreEntry')
class PaymentStoreTable extends Table {
  @override
  String get tableName => 'payment_store';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  IntColumn get userId => integer().nullable()(); // Customer or Vendor ID
  TextColumn get userClientUuid => text().nullable()();
  
  IntColumn get orderId => integer().nullable()();
  TextColumn get orderClientUuid => text().nullable()();
  
  IntColumn get purchaseId => integer().nullable()();
  TextColumn get purchaseClientUuid => text().nullable()();
  
  IntColumn get customInvoiceId => integer().nullable()();
  
  RealColumn get amount => real().withDefault(const Constant(0.0))();
  RealColumn get paymentAmount => real().withDefault(const Constant(0.0))();
  DateTimeColumn get paymentDate => dateTime()();
  
  TextColumn get paymentMethod => text().withDefault(const Constant('cash'))(); // 'cash', 'online', 'scan', 'debit card', 'upi', 'cheque'
  TextColumn get paymentType => text().nullable()();
  RealColumn get cashAmount => real().withDefault(const Constant(0.0))();
  RealColumn get upiAmount => real().withDefault(const Constant(0.0))();
  RealColumn get remainingAmount => real().withDefault(const Constant(0.0))();
  
  IntColumn get bankId => integer().nullable()();
  TextColumn get bankClientUuid => text().nullable()();
  
  TextColumn get status => text().withDefault(const Constant('credit'))(); // 'credit' (sales receipt/inflow), 'debit' (purchase payment/outflow)
  TextColumn get remarks => text().nullable()();
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

### 3.5. Orders & Order Items (`orders`, `order_items`)

```dart
@DataClassName('OrderEntry')
class OrdersTable extends Table {
  @override
  String get tableName => 'orders';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  TextColumn get orderNumber => text()();
  IntColumn get userId => integer().nullable()(); // Customer ID
  TextColumn get userClientUuid => text().nullable()();
  
  IntColumn get tableNo => integer().nullable()();
  TextColumn get orderType => text().withDefault(const Constant('dine_in'))(); // 'dine_in', 'takeaway', 'delivery', 'room_service'
  
  RealColumn get subtotal => real().withDefault(const Constant(0.0))();
  RealColumn get discount => real().withDefault(const Constant(0.0))();
  RealColumn get taxAmount => real().withDefault(const Constant(0.0))();
  RealColumn get totalAmount => real().withDefault(const Constant(0.0))();
  RealColumn get paidAmount => real().withDefault(const Constant(0.0))();
  RealColumn get remainingAmount => real().withDefault(const Constant(0.0))();
  
  TextColumn get paymentStatus => text().withDefault(const Constant('pending'))(); // 'completed', 'pending', 'partial'
  TextColumn get paymentMethod => text().nullable()();
  TextColumn get deliveryStatus => text().withDefault(const Constant('pending'))(); // 'pending', 'running', 'ready', 'served', 'delivered', 'cancelled'
  
  IntColumn get createdBy => integer().nullable()();
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}

@DataClassName('OrderItemEntry')
class OrderItemsTable extends Table {
  @override
  String get tableName => 'order_items';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  
  IntColumn get orderId => integer().nullable()();
  TextColumn get orderClientUuid => text().nullable()();
  
  IntColumn get productId => integer().nullable()();
  TextColumn get productClientUuid => text().nullable()();
  
  RealColumn get quantity => real().withDefault(const Constant(1.0))();
  RealColumn get price => real().withDefault(const Constant(0.0))();
  RealColumn get totalAmount => real().withDefault(const Constant(0.0))();
  
  IntColumn get categoryId => integer().nullable()();
  IntColumn get quantityTypeId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  IntColumn get userId => integer().nullable()();
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

### 3.6. Purchases & Invoices (`purchases`, `purchase_invoice`)

```dart
@DataClassName('PurchaseInvoiceEntry')
class PurchaseInvoiceTable extends Table {
  @override
  String get tableName => 'purchase_invoice';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  TextColumn get invoiceNumber => text()();
  IntColumn get vendorId => integer().nullable()();
  TextColumn get vendorClientUuid => text().nullable()();
  
  RealColumn get totalAmount => real().withDefault(const Constant(0.0))();
  RealColumn get discount => real().withDefault(const Constant(0.0))();
  RealColumn get taxAmount => real().withDefault(const Constant(0.0))();
  RealColumn get grandTotal => real().withDefault(const Constant(0.0))();
  RealColumn get paidAmount => real().withDefault(const Constant(0.0))();
  RealColumn get remainingAmount => real().withDefault(const Constant(0.0))();
  
  TextColumn get products => text().nullable()(); // JSON string of purchase bill items
  IntColumn get createdBy => integer().nullable()();
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}

@DataClassName('PurchasesEntry')
class PurchasesTable extends Table {
  @override
  String get tableName => 'purchases';

  IntColumn get id => integer().autoIncrement()();
  TextColumn get clientUuid => text().unique()();
  IntColumn get remoteId => integer().nullable()();
  IntColumn get branchId => integer().nullable()();
  
  IntColumn get invoiceId => integer().nullable()();
  TextColumn get invoiceClientUuid => text().nullable()();
  
  IntColumn get vendorId => integer().nullable()();
  TextColumn get vendorClientUuid => text().nullable()();
  
  TextColumn get paymentStatus => text().withDefault(const Constant('unpaid'))(); // 'paid', 'unpaid', 'partial'
  
  BoolColumn get isDeleted => boolean().withDefault(const Constant(false))();
  BoolColumn get isDirty => boolean().withDefault(const Constant(false))();
  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get syncedAt => dateTime().nullable()();
}
```

---

## 4. Two-Way Sync Engine: Pull & Push Payloads

### 4.1. Delta Pull Payload (`POST /api/v1/sync/pull`)

The client requests delta records since the last successful sync:

```http
POST /api/v1/sync/pull HTTP/1.1
Host: hotel-erp.yourdomain.com
Content-Type: application/json
X-License-Key: 23B8A
Authorization: Bearer <JWT_OR_SANCTUM_TOKEN>

{
  "branch_id": 1,
  "last_sync_timestamp": "2026-11-04 10:00:00",
  "tables": [
    "bank_master",
    "expense_types",
    "expenses",
    "payment_store",
    "orders",
    "order_items",
    "purchases",
    "purchase_invoice",
    "taxes"
  ]
}
```

#### Response Structure:
```json
{
  "status": true,
  "sync_timestamp": "2026-11-05 17:30:00",
  "data": {
    "bank_master": [
      {
        "id": 3,
        "client_uuid": "bank-uuid-001",
        "branch_id": 1,
        "bank_name": "State Bank of India",
        "account_number": "30981234567",
        "ifsc_code": "SBIN0001234",
        "branch_name": "Main Branch",
        "opening_balance": "50000.00",
        "status": "active",
        "isDeleted": 0,
        "updated_at": "2026-11-05 11:20:00"
      }
    ],
    "expense_types": [
      {
        "id": 1,
        "client_uuid": "exptype-uuid-01",
        "branch_id": 1,
        "type": "Dairy & Milk",
        "isDeleted": 0,
        "updated_at": "2026-11-05 09:15:00"
      }
    ],
    "expenses": [
      {
        "id": 14,
        "client_uuid": "exp-uuid-999",
        "branch_id": 1,
        "sr_no": "EXP-0014",
        "expense_name": "Fresh Milk Supply",
        "expense_date": "2026-11-05",
        "amount": "1450.00",
        "expense_type_id": 1,
        "payment_mode": "Cash",
        "bank_id": null,
        "description": "25 Liters Amul Gold",
        "isDeleted": 0,
        "updated_at": "2026-11-05 09:30:00"
      }
    ],
    "payment_store": [
      {
        "id": 88,
        "client_uuid": "pay-uuid-777",
        "branch_id": 1,
        "order_id": 42,
        "order_client_uuid": "ord-uuid-42",
        "amount": "820.00",
        "payment_amount": "820.00",
        "payment_date": "2026-11-05 13:10:00",
        "payment_method": "online",
        "bank_id": 3,
        "status": "credit",
        "remarks": "GooglePay Txn #GP987654",
        "isDeleted": 0,
        "updated_at": "2026-11-05 13:10:00"
      }
    ]
  },
  "deleted_ids": {
    "expenses": [12],
    "bank_master": []
  }
}
```

---

### 4.2. Batch Push Payload (`POST /api/v1/sync/push`)

When the desktop application recovers internet connectivity or saves records locally, dirty records are submitted:

```http
POST /api/v1/sync/push HTTP/1.1
Host: hotel-erp.yourdomain.com
Content-Type: application/json
X-License-Key: 23B8A
Authorization: Bearer <JWT_OR_SANCTUM_TOKEN>

{
  "branch_id": 1,
  "payload": {
    "bank_master": [
      {
        "local_id": 5,
        "client_uuid": "bank-uuid-local-02",
        "bank_name": "HDFC Current Account",
        "account_number": "50200012345678",
        "ifsc_code": "HDFC0000240",
        "branch_name": "City Center",
        "opening_balance": 15000.0,
        "status": "active",
        "isDeleted": 0
      }
    ],
    "expense_types": [
      {
        "local_id": 8,
        "client_uuid": "exptype-local-08",
        "type": "Kitchen Gas Cylinder",
        "isDeleted": 0
      }
    ],
    "expenses": [
      {
        "local_id": 23,
        "client_uuid": "exp-local-23",
        "sr_no": "EXP-0023",
        "expense_name": "HP Commercial LPG 19kg",
        "expense_date": "2026-11-05",
        "amount": 2100.0,
        "expense_type_id": 8,
        "payment_mode": "Bank",
        "bank_id": 5,
        "description": "Refill for Main Kitchen",
        "isDeleted": 0
      }
    ],
    "orders": [
      {
        "local_id": 105,
        "client_uuid": "ord-local-105",
        "order_number": "ORD-20261105-0105",
        "table_no": 4,
        "order_type": "dine_in",
        "subtotal": 1200.0,
        "discount": 50.0,
        "tax_amount": 57.5,
        "total_amount": 1207.5,
        "paid_amount": 1207.5,
        "remaining_amount": 0.0,
        "payment_status": "completed",
        "payment_method": "online",
        "delivery_status": "served",
        "created_at": "2026-11-05 14:00:00"
      }
    ],
    "payment_store": [
      {
        "local_id": 140,
        "client_uuid": "pay-local-140",
        "order_client_uuid": "ord-local-105",
        "amount": 1207.5,
        "payment_amount": 1207.5,
        "payment_date": "2026-11-05 14:10:00",
        "payment_method": "online",
        "bank_id": 5,
        "status": "credit",
        "remarks": "UPI Scanner settlement"
      }
    ]
  }
}
```

#### Push Response & Mapping:
```json
{
  "status": true,
  "message": "Sync batch processed successfully.",
  "synced_mappings": {
    "bank_master": [
      { "local_id": 5, "remote_id": 4, "client_uuid": "bank-uuid-local-02" }
    ],
    "expense_types": [
      { "local_id": 8, "remote_id": 9, "client_uuid": "exptype-local-08" }
    ],
    "expenses": [
      { "local_id": 23, "remote_id": 54, "client_uuid": "exp-local-23" }
    ],
    "orders": [
      { "local_id": 105, "remote_id": 92, "client_uuid": "ord-local-105" }
    ],
    "payment_store": [
      { "local_id": 140, "remote_id": 115, "client_uuid": "pay-local-140" }
    ]
  }
}
```

*Client Action:* For each item in `synced_mappings`, update the local Drift row:
```dart
await (update(db.expensesTable)..where((t) => t.clientUuid.equals(clientUuid)))
    .write(ExpensesTableCompanion(
      remoteId: Value(remoteId),
      isDirty: const Value(false),
      syncedAt: Value(DateTime.now()),
    ));
```

---

## 5. Offline Drift SQL Computation & Ledgers

In offline mode, financial books and ledgers must be calculated locally using SQLite queries on the Drift database.

### 5.1. Module 1: Account Ledger (Customer & Vendor Running Balances)

The Account Ledger tracks debtor/creditor balances over a selected period.

#### 1. Customer Ledger:
- **Debit (+):** Total value of sales invoices/orders placed by the customer.
- **Credit (-):** Payments received from the customer (`payment_store`).
- **Balance:** `Running Balance = Previous Balance + Debits - Credits`.

#### 2. Vendor Ledger:
- **Credit (+):** Total value of purchases/bills received from the vendor (`purchase_invoice`).
- **Debit (-):** Payments issued to the vendor (`payment_store` with `purchase_id`).
- **Balance:** `Running Balance = Previous Balance + Credits - Debits`.

#### Dart Implementation for Drift:
```dart
class LedgerTransaction {
  final DateTime date;
  final String referenceNo;
  final String particulars;
  final double debit;
  final double credit;
  final double runningBalance;

  LedgerTransaction({
    required this.date,
    required this.referenceNo,
    required this.particulars,
    required this.debit,
    required this.credit,
    required this.runningBalance,
  });
}

Future<List<LedgerTransaction>> getCustomerLedger({
  required AppDatabase db,
  required int customerId,
  required DateTime startDate,
  required DateTime endDate,
}) async {
  // 1. Fetch sales orders (Debits)
  final orders = await (db.select(db.ordersTable)
        ..where((t) =>
            t.userId.equals(customerId) &
            t.isDeleted.equals(false) &
            t.createdAt.isBiggerOrEqualValue(startDate) &
            t.createdAt.isSmallerOrEqualValue(endDate))
        ..orderBy([(t) => OrderingTerm.asc(t.createdAt)]))
      .get();

  // 2. Fetch payments received (Credits)
  final payments = await (db.select(db.paymentStoreTable)
        ..where((t) =>
            t.userId.equals(customerId) &
            t.isDeleted.equals(false) &
            t.status.equals('credit') &
            t.paymentDate.isBiggerOrEqualValue(startDate) &
            t.paymentDate.isSmallerOrEqualValue(endDate))
        ..orderBy([(t) => OrderingTerm.asc(t.paymentDate)]))
      .get();

  // 3. Merge & Sort Chronologically
  List<LedgerTransaction> ledger = [];
  double balance = 0.0;

  for (final o in orders) {
    balance += o.totalAmount;
    ledger.add(LedgerTransaction(
      date: o.createdAt,
      referenceNo: o.orderNumber,
      particulars: 'Sales Order (${o.orderType})',
      debit: o.totalAmount,
      credit: 0.0,
      runningBalance: balance,
    ));
  }

  for (final p in payments) {
    balance -= p.paymentAmount;
    ledger.add(LedgerTransaction(
      date: p.paymentDate,
      referenceNo: p.clientUuid.substring(0, 8),
      particulars: 'Receipt (${p.paymentMethod})',
      debit: 0.0,
      credit: p.paymentAmount,
      runningBalance: balance,
    ));
  }

  ledger.sort((a, b) => a.date.compareTo(b.date));
  return ledger;
}
```

---

### 5.2. Module 2: Income Statement (Profit & Loss Statement)

The Income Statement calculates Operating Profit/Loss across any date range:
1. **Total Sales Revenue:** `SUM(orders.total_amount)` where `payment_status = 'completed'`.
   - *Split:* Cash Sales (`payment_store.payment_method = 'cash'`) vs Online/Digital Sales.
2. **Cost of Goods Sold (COGS / Purchases):** `SUM(purchase_invoice.grand_total)`.
   - *Split:* Cash Purchases vs Bank/Credit Purchases.
3. **Gross Profit:** `Total Sales Revenue - Cost of Goods Sold`.
4. **Operating Expenses:** `SUM(expenses.amount)`.
5. **Net Operating Income:** `Gross Profit - Operating Expenses`.

#### Dart Implementation for Drift:
```dart
class IncomeStatementResult {
  final double totalSales;
  final double salesCash;
  final double salesOnline;
  final double totalPurchases;
  final double grossProfit;
  final double totalExpenses;
  final double netOperatingIncome;

  IncomeStatementResult({
    required this.totalSales,
    required this.salesCash,
    required this.salesOnline,
    required this.totalPurchases,
    required this.grossProfit,
    required this.totalExpenses,
    required this.netOperatingIncome,
  });
}

Future<IncomeStatementResult> calculateIncomeStatement({
  required AppDatabase db,
  required DateTime startDate,
  required DateTime endDate,
}) async {
  // 1. Sales
  final salesQuery = db.select(db.ordersTable)
    ..where((t) =>
        t.isDeleted.equals(false) &
        t.paymentStatus.equals('completed') &
        t.createdAt.isBiggerOrEqualValue(startDate) &
        t.createdAt.isSmallerOrEqualValue(endDate));
  final salesList = await salesQuery.get();
  final totalSales = salesList.fold<double>(0.0, (acc, item) => acc + item.totalAmount);

  // 2. Sales Payment Split from payment_store
  final payments = await (db.select(db.paymentStoreTable)
        ..where((t) =>
            t.isDeleted.equals(false) &
            t.status.equals('credit') &
            t.paymentDate.isBiggerOrEqualValue(startDate) &
            t.paymentDate.isSmallerOrEqualValue(endDate)))
      .get();
  final salesCash = payments
      .where((p) => p.paymentMethod.toLowerCase() == 'cash')
      .fold<double>(0.0, (acc, item) => acc + item.paymentAmount);
  final salesOnline = payments
      .where((p) => p.paymentMethod.toLowerCase() != 'cash')
      .fold<double>(0.0, (acc, item) => acc + item.paymentAmount);

  // 3. Purchases (COGS)
  final purchaseInvoices = await (db.select(db.purchaseInvoiceTable)
        ..where((t) =>
            t.isDeleted.equals(false) &
            t.createdAt.isBiggerOrEqualValue(startDate) &
            t.createdAt.isSmallerOrEqualValue(endDate)))
      .get();
  final totalPurchases = purchaseInvoices.fold<double>(0.0, (acc, item) => acc + item.grandTotal);

  final grossProfit = totalSales - totalPurchases;

  // 4. Expenses
  final expenseList = await (db.select(db.expensesTable)
        ..where((t) =>
            t.isDeleted.equals(false) &
            t.expenseDate.isBiggerOrEqualValue(startDate) &
            t.expenseDate.isSmallerOrEqualValue(endDate)))
      .get();
  final totalExpenses = expenseList.fold<double>(0.0, (acc, item) => acc + item.amount);

  final netIncome = grossProfit - totalExpenses;

  return IncomeStatementResult(
    totalSales: totalSales,
    salesCash: salesCash,
    salesOnline: salesOnline,
    totalPurchases: totalPurchases,
    grossProfit: grossProfit,
    totalExpenses: totalExpenses,
    netOperatingIncome: netIncome,
  );
}
```

---

### 5.3. Module 3: Bank Book (Digital & Bank Ledgers)

The Bank Book records all digital collections and bank disbursements:
- **Receipts / Inflows (Credit):** `payment_store` entries with `payment_method` in `['online', 'scan', 'upi', 'debit card', 'cheque', 'bank_transfer']` or where `bank_id` is linked.
- **Disbursements / Outflows (Debit):** `payment_store` supplier payments via bank, plus `expenses` with `payment_mode = 'Bank'`.
- **Closing Balance:** `Bank Opening Balance + Total Receipts - Total Disbursements`.

```dart
class BankBookEntry {
  final DateTime date;
  final String txnNumber;
  final String particulars;
  final String paymentMethod;
  final double credit; // Inward collection
  final double debit;  // Outward disbursement
  final double balance;

  BankBookEntry({
    required this.date,
    required this.txnNumber,
    required this.particulars,
    required this.paymentMethod,
    required this.credit,
    required this.debit,
    required this.balance,
  });
}
```

---

### 5.4. Module 4: Cash Book (Daily Cash Inflows & Outflows)

The Cash Book maintains strict offline physical cash tracking:
- **Cash In (Credit):**
  - Customer order settlements where `payment_method = 'cash'`.
  - Cash advance payments.
- **Cash Out (Debit):**
  - Cash expenses (`expenses` where `payment_mode = 'Cash'`).
  - Cash purchase bills settled with suppliers (`payment_store` where `status = 'debit'` and `payment_method = 'cash'`).
- **Net Cash in Hand:** `Previous Cash + Cash In - Cash Out`.

#### Dart Implementation for Drift:
```dart
Future<Map<String, dynamic>> getCashBookReport({
  required AppDatabase db,
  required DateTime startDate,
  required DateTime endDate,
}) async {
  // 1. Inward Cash from Orders & Payments
  final cashPayments = await (db.select(db.paymentStoreTable)
        ..where((t) =>
            t.isDeleted.equals(false) &
            t.paymentMethod.lower().equals('cash') &
            t.paymentDate.isBiggerOrEqualValue(startDate) &
            t.paymentDate.isSmallerOrEqualValue(endDate)))
      .get();

  final cashIn = cashPayments
      .where((p) => p.status == 'credit')
      .fold<double>(0.0, (acc, item) => acc + item.paymentAmount);

  final cashOutPurchases = cashPayments
      .where((p) => p.status == 'debit')
      .fold<double>(0.0, (acc, item) => acc + item.paymentAmount);

  // 2. Outward Cash from Daily Expenses
  final cashExpenses = await (db.select(db.expensesTable)
        ..where((t) =>
            t.isDeleted.equals(false) &
            t.paymentMode.lower().equals('cash') &
            t.expenseDate.isBiggerOrEqualValue(startDate) &
            t.expenseDate.isSmallerOrEqualValue(endDate)))
      .get();

  final totalCashExpenses = cashExpenses.fold<double>(0.0, (acc, item) => acc + item.amount);
  final totalCashOut = cashOutPurchases + totalCashExpenses;
  final netCashInHand = cashIn - totalCashOut;

  return {
    'cash_in': cashIn,
    'cash_out_purchases': cashOutPurchases,
    'cash_out_expenses': totalCashExpenses,
    'total_cash_out': totalCashOut,
    'net_cash_in_hand': netCashInHand,
  };
}
```

---

## 6. Offline Drift SQL Computation for Reports

### 6.1. Module 5: GST Purchase Report (GSTR-2 ITC Reconciliation)

GSTR-2 reconciles Inward Supplies and Input Tax Credit (ITC):
- **B2B Registered Invoices:** Supplier has a valid 15-character GSTIN (`users.gst_number` or `user_details.gst_number`).
- **B2UR Unregistered Invoices:** Purchases from vendors without a GSTIN.
- **Tax Breakdown:**
  - Taxable Value = `grand_total - tax_amount`.
  - Intra-State (State matches branch): CGST (50%) + SGST (50%).
  - Inter-State (State differs): IGST (100%).

```dart
class GstPurchaseSummary {
  final double b2bTaxable;
  final double b2bCgst;
  final double b2bSgst;
  final double b2bIgst;
  final double b2urTaxable;
  final double totalItcClaimable;

  GstPurchaseSummary({
    required this.b2bTaxable,
    required this.b2bCgst,
    required this.b2bSgst,
    required this.b2bIgst,
    required this.b2urTaxable,
    required this.totalItcClaimable,
  });
}
```

---

### 6.2. Module 6: GST Sales Report (GSTR-1 Outward Supplies)

GSTR-1 breaks down restaurant/hotel sales:
- **B2B Invoices (Table 4A):** Dining or corporate orders where customer GSTIN is provided.
- **B2C Large / Small (Table 5 & 7):** Standard hotel dining, KOT bills, and takeaway bills.
- **Tax Rate Computation:** Typically 5% GST on Restaurant Services (2.5% CGST + 2.5% SGST without ITC) or 18% on AC banquet/alcohol services.

---

### 6.3. Module 7: Purchase Report

Summarizes vendor bills with payment status:
- Filterable by date, vendor, branch, payment status (`paid`, `unpaid`, `partial`).
- Fields: `Invoice Number`, `Vendor Name`, `Bill Date`, `Grand Total`, `Paid Amount`, `Due Amount`, `Payment Status`.

---

### 6.4. Module 8: Expense Report

Categorizes operational expenses:
- Grouped by `ExpenseType` (Kitchen, Utilities, Repairs, Staff Food, Salaries).
- Breakdown by `Payment Mode` (Cash vs Bank).
- Summary Metrics: Total Expenses, Top 3 Expense Categories, Average Daily Expense.

---

### 6.5. Module 9: Orders Report (POS & Dining Sales)

Provides POS analytics:
- Filterable by: Dining Table, Order Type (`dine_in`, `takeaway`, `delivery`, `room_service`), Waiter, Payment Status, Delivery Status.
- Metrics: Total Orders Count, Total Revenue, Average Order Value (AOV), Total CGST & SGST Collected, Cash vs UPI Split.

---

## 7. Dedicated Server REST APIs & Export Endpoints

When the application is connected to the internet, these dedicated endpoints are used for server-side generation, official PDF downloads, and Excel exports:

### 7.1. Account Ledger & Banking APIs

| Route | Method | Purpose | Key Parameters |
|---|---|---|---|
| `/api/account-ledger` | `GET` | Paginated account ledger summary | `type` (`customer`/`vendor`), `month`, `year` |
| `/api/get-payment-details` | `GET` | Detailed debit/credit transaction breakdown | `type`, `customer_id`, `vendor_id`, `month`, `year` |
| `/api/export-account-ledger` | `GET` | Export account ledger to Excel (`.xlsx`) | `type`, `customer_id`, `vendor_id` |
| `/api/account-ledger/download-pdf`| `POST`| Generate official PDF statement | `type`, `customer_id`, `vendor_id`, `start_date`, `end_date` |
| `/api/all-banks` | `GET` | Fetch all bank master accounts | None |
| `/api/banks` | `POST`| Add new bank account | `bank_name`, `account_number`, `ifsc_code`, `opening_balance` |
| `/api/bankbook` | `GET` | Fetch bank book transactions | `bank_id`, `from_date`, `to_date`, `search`, `page` |
| `/api/export-bankbook-pdf` | `GET` | Download Bank Book PDF | `bank_id`, `from_date`, `to_date` |
| `/api/export-bankbook-excel` | `GET` | Export Bank Book Excel | `bank_id`, `from_date`, `to_date` |
| `/api/cashbook/data` | `GET` | Fetch daily cash book transactions | `from_date`, `to_date`, `status` (`expense`/null), `page` |
| `/api/export-cashbook-pdf` | `GET` | Download Cash Book PDF | `from_date`, `to_date` |
| `/api/export-cashbook-excel` | `GET` | Export Cash Book Excel | `from_date`, `to_date` |

---

### 7.2. Expenses & Masters APIs

| Route | Method | Purpose | Key Parameters |
|---|---|---|---|
| `/api/expenses/list` | `GET` | List all expenses with pagination | `page`, `per_page`, `search`, `from_date`, `to_date` |
| `/api/expenses/store` | `POST`| Create a new expense | `sr_no`, `expense_name`, `expense_date`, `amount`, `expense_type_id`, `payment_mode` (`Cash`/`Bank`) |
| `/api/expenses/{id}` | `POST`| Soft-delete expense | `id` |
| `/api/expense-types` | `GET` | List all expense categories | None |
| `/api/expense-type/store` | `POST`| Create new expense type | `type` (name) |
| `/api/expenses/report` | `GET` | Detailed Expense Report | `from_date`, `to_date`, `expense_type_id`, `payment_mode` |
| `/api/expense/report/export-pdf-api` | `POST`| Export Expense Report to PDF | `from_date`, `to_date` |

---

### 7.3. GST & Operational Reporting APIs

| Route | Method | Purpose | Key Parameters |
|---|---|---|---|
| `/api/income-statement` | `GET` | Full P&L Statement (Sales, COGS, Expenses, Net) | `start_date`, `end_date` |
| `/income-statement/pdf` | `GET` | Download Income Statement PDF | `start_date`, `end_date` |
| `/income-statement/excel` | `GET` | Download Income Statement Excel | `start_date`, `end_date` |
| `/api/gst/gstr-1/data` | `GET` | GSTR-1 Sales Report (B2B, B2C, HSN) | `filter` (`this_month`, etc.) or `from_date`, `to_date` |
| `/api/gst/gstr-1/export-excel` | `GET` | GSTR-1 Official Govt Excel Template | `from_date`, `to_date` |
| `/api/gst/gstr-2/data` | `GET` | GSTR-2 Purchase ITC Report | `filter` or `from_date`, `to_date` |
| `/api/gst/gstr-2/pdf` | `GET` | GSTR-2 Purchase ITC PDF | `from_date`, `to_date` |
| `/api/gst/gstr-2/export-excel` | `GET` | GSTR-2 Purchase ITC Excel | `from_date`, `to_date` |
| `/api/gstr-3b` | `GET` | GSTR-3B Monthly Return Summary | `filter` or `from_date`, `to_date` |
| `/api/purchase-report-data` | `GET` | Purchases Report data | `from_date`, `to_date`, `vendor_id`, `status` |
| `/api/purchases/report/export-pdf-api` | `POST` | Purchase Report PDF | `from_date`, `to_date` |
| `/api/order-report` | `GET` | Hotel Dining / Orders Report | `from_date`, `to_date`, `table_no`, `order_type` |
| `/api/sales/report/export-pdf-api` | `POST` | Orders / Sales Report PDF | `from_date`, `to_date` |

---

## 8. Edge Cases, Split Payments & Conflict Resolution

### 1. Multi-Tender Split Payments (Cash + UPI)
When a customer pays part in Cash and part via Online UPI (e.g. ₹1000 bill paid as ₹400 Cash + ₹600 UPI):
- Insert **two** entries into `payment_store` with the same `order_client_uuid`:
  1. Entry 1: `payment_amount = 400.0`, `payment_method = 'cash'`, `status = 'credit'`
  2. Entry 2: `payment_amount = 600.0`, `payment_method = 'online'`, `bank_id = <bank_id>`, `status = 'credit'`
- *Result:* The Cash Book receives ₹400, the Bank Book receives ₹600, and the Account Ledger credits ₹1000.

### 2. Client-Generated UUID Primary Keys
Never rely on auto-incrementing integer IDs (`id`) for offline-created records.
- All offline records (`expenses`, `payment_store`, `orders`) must generate a `client_uuid = Uuid().v4()`.
- Foreign key references link to `client_uuid` first. When synced, the server responds with `remote_id` mappings.

### 3. Deleted Records Sync
- When a user deletes an expense or cancels an order locally while offline, mark `is_deleted = true` and `is_dirty = true`.
- During sync push, send `isDeleted: 1`.
- The server will execute soft-deletion without losing referential integrity:
```php
$expense = \App\Models\Expense::firstOrNew(['client_uuid' => $uuid]);
$expense->isDeleted = 1;
$expense->save();
```

---

## 9. Verification & Testing Checklist

- [x] **Banks:** Create a bank offline -> Verify `bank_master` Drift insert -> Push to `/api/v1/sync/push` -> Verify remote ID.
- [x] **New Expenses:** Create a Cash expense -> Verify instant deduction in offline Cash Book -> Push -> Verify in Web Admin.
- [x] **Account Ledger:** Complete a credit sale to customer -> Punch payment receipt -> Verify running balance updates in Drift DB.
- [x] **Income Statement:** Punch sales order + purchase bill + expense -> Verify formula: `(Sales - COGS - Expenses) = Net Operating Income`.
- [x] **GST Reports:** Verify B2B orders with customer GSTIN match GSTR-1, and supplier invoices match GSTR-2.
- [x] **PDF & Excel Exports:** Test online direct export routes for official CA and audit reporting.

