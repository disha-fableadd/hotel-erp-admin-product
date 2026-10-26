# Hotel ERP

Hotel ERP is a Laravel-based management system for sales, purchase, inventory, invoices, staff, attendance, salary, and accounting reports.

## 🚀 Features

- **POS (Point of Sale) & Sales Management:** Interactive billing interface, POS dashboard, sales reports, sales returns, detailed invoices, and multi-branch sales tracking.
- **Purchase Management:** Vendor purchases management, purchase return logs, purchase invoice creation, and detailed purchase order reporting.
- **Inventory & Stock Tracking:** Catalog management with categories, brands, products, custom quantity types, stock levels monitoring, low-stock notifications, and inventory reports.
- **Invoice & Billing Workflows:** Support for generating POS invoices, purchase invoices, and fully customizable custom invoices, complete with payment history tracking and PDF export templates.
- **Staff & HR Management:** Department and designation setups, custom company rules, holiday calendars, leave type settings, and employee leaves administration with an approval workflow.
- **Biometric Face-Recognition Attendance:** Integrated face-login and check-in/out attendance tracking, grace period configuration, and automated calculation of late minutes, overtime, and meal breaks.
- **Payroll & Salary Administration:** Advanced payroll sheets, salary listings, advance payment tracking, group salary processing, and automatic payslip generation and download.
- **Accounting & Ledgers:** Account ledger lists, balance sheets, income statements (with Excel and PDF export options), and expense category/type tracking.
- **Government GST Tax Reporting:** Tax compliance reporting with exports for GSTR-1, GSTR-2, GSTR-3B, GSTR-9, and GSTR-9C for both sales and purchases.
- **Role-Based Access Control (RBAC):** Granular roles and permissions handled via Spatie (`admin`, `sub-admin`, `staff`, `customer`, `vendor`) for secure web and API authentication via Laravel Sanctum & Passport.
- **Notifications System:** Dynamic alert system to manage and track system events and notifications.
- **Appointments & Booking Management:** Reservation tracking with detailed logs, customer contact info, and vehicle booking details (number, model, brand, type).

## 🛠️ Technology Stack

- Backend: Laravel 10 (PHP 8.1+)
- Frontend tooling: Vite 5, Axios
- Database: MySQL / MariaDB
- PDF generation: `barryvdh/laravel-dompdf`
- Excel/Spreadsheet export: `phpoffice/phpspreadsheet`
- Barcode support: `milon/barcode`
- Roles and permissions: `spatie/laravel-permission`
- API Authentication packages: `laravel/sanctum`, `laravel/passport`

## 📦 Installation

### Requirements

- PHP 8.1+
- Composer
- MySQL / MariaDB
- Node.js and npm (required if you build frontend assets)

### Setup Steps

1. Install PHP dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```
Then update `.env` with your database credentials.

3. Generate app key:
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Seed roles and admin user:
```bash
php artisan db:seed
```
This seeds:
- Roles: `admin`, `sub-admin`, `staff`, `customer`, `vendor`
- Admin user with assigned admin role

6. (Optional) Install frontend dependencies and build assets:
```bash
npm install
npm run build
```

7. Start application:
```bash
php artisan serve
```

## 🔐 Authentication (Important)

This project uses multiple authentication-related layers:

- Session-based login for the web application
- Role-based authorization with `spatie/laravel-permission`
- API token/auth support via `laravel/sanctum` and `laravel/passport` packages


### Default Seeded Credentials

- Email: `admin@gmail.com`
- Password: `12345678`

### Seeded Roles

- `admin`
- `sub-admin`
- `staff`
- `customer`
- `vendor`

### Notes

- Change the default admin password immediately after first login.
- If Passport-based API authentication is needed, run:
```bash
php artisan passport:install
```
- Ensure authentication guards/providers are configured correctly in `config/auth.php` based on your usage (web/API).

## Useful Commands

- Run only admin seeder:
```bash
php artisan db:seed --class=AdminUserSeeder
```

- Run only roles seeder:
```bash
php artisan db:seed --class=RolesSeeder
```

- Fresh migration + seed:
```bash
php artisan migrate:fresh --seed
```

## License
 
This project is proprietary software developed by **Fablead Developers Technolab**. All rights reserved.
 
