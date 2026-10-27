<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDataSeeder extends Seeder
{
    /**
     * Seed essential ERP and HR master tables.
     */
    public function run(): void
    {
        $branchId = 1;
        $adminUserId = User::where('role', 'admin')->value('id')
            ?? DB::table('users')->value('id');

        // 1. Taxes Master (GST rates)
        if (Schema::hasTable('taxes')) {
            $taxes = [
                ['tax_name' => 'CGST', 'tax_rate' => 5.00, 'status' => 'active'],
                ['tax_name' => 'SGST', 'tax_rate' => 5.00, 'status' => 'active'],
                ['tax_name' => 'IGST', 'tax_rate' => 5.00, 'status' => 'active'],
                ['tax_name' => 'GST 0%', 'tax_rate' => 0.00, 'status' => 'active'],
                ['tax_name' => 'GST 5%', 'tax_rate' => 5.00, 'status' => 'active'],
                ['tax_name' => 'GST 12%', 'tax_rate' => 12.00, 'status' => 'active'],
                ['tax_name' => 'GST 18%', 'tax_rate' => 18.00, 'status' => 'active'],
                ['tax_name' => 'GST 28%', 'tax_rate' => 28.00, 'status' => 'active'],
            ];

            foreach ($taxes as $tax) {
                DB::table('taxes')->updateOrInsert(
                    [
                        'tax_name' => $tax['tax_name'],
                        'branch_id' => $branchId,
                    ],
                    [
                        'tax_rate' => $tax['tax_rate'],
                        'status' => $tax['status'],
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 2. Currency Master
        if (Schema::hasTable('currency')) {
            $currencies = [
                ['currency_name' => 'Indian Rupee', 'currency_symbol' => '₹'],
                ['currency_name' => 'US Dollar', 'currency_symbol' => '$'],
                ['currency_name' => 'Euro', 'currency_symbol' => '€'],
                ['currency_name' => 'British Pound', 'currency_symbol' => '£'],
                ['currency_name' => 'UAE Dirham', 'currency_symbol' => 'AED'],
            ];

            foreach ($currencies as $curr) {
                DB::table('currency')->updateOrInsert(
                    ['currency_name' => $curr['currency_name']],
                    [
                        'currency_symbol' => $curr['currency_symbol'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 3. Departments & Designations Master (Skipped dummy seeding per client requirements)

        // 4. Leave Types Master
        if (Schema::hasTable('leave_type')) {
            $leaveTypes = [
                ['leave_type' => 'Casual Leave', 'number_of_leaves' => 12, 'allow_half_day' => 1, 'requires_approval' => 1],
                ['leave_type' => 'Sick Leave', 'number_of_leaves' => 10, 'allow_half_day' => 1, 'requires_approval' => 1],
                ['leave_type' => 'Earned Leave', 'number_of_leaves' => 15, 'allow_half_day' => 0, 'requires_approval' => 1],
                ['leave_type' => 'Leave Without Pay', 'number_of_leaves' => 0, 'allow_half_day' => 1, 'requires_approval' => 1],
            ];

            foreach ($leaveTypes as $lt) {
                DB::table('leave_type')->updateOrInsert(
                    [
                        'leave_type' => $lt['leave_type'],
                        'branch_id' => $branchId,
                    ],
                    [
                        'number_of_leaves' => $lt['number_of_leaves'],
                        'allow_half_day' => $lt['allow_half_day'],
                        'requires_approval' => $lt['requires_approval'],
                        'created_by' => $adminUserId ?: 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 5. Holiday Calendar Master
        if (Schema::hasTable('holiday_calendar')) {
            $holidays = [
                ['title' => 'New Year Day', 'holiday_date' => '2026-01-01', 'description' => 'New Year Celebration'],
                ['title' => 'Republic Day', 'holiday_date' => '2026-01-26', 'description' => 'Indian Republic Day'],
                ['title' => 'Independence Day', 'holiday_date' => '2026-08-15', 'description' => 'Indian Independence Day'],
                ['title' => 'Gandhi Jayanti', 'holiday_date' => '2026-10-02', 'description' => 'Mahatma Gandhi Jayanti'],
                ['title' => 'Diwali', 'holiday_date' => '2026-11-08', 'description' => 'Festival of Lights'],
            ];

            foreach ($holidays as $h) {
                DB::table('holiday_calendar')->updateOrInsert(
                    [
                        'title' => $h['title'],
                        'holiday_date' => $h['holiday_date'],
                        'branch_id' => $branchId,
                    ],
                    [
                        'description' => $h['description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 6. Product Categories Master (Skipped per client requirements)
        // 7. Product Brands Master (Skipped per client requirements)

        // 8. Quantity Types (Units) Master
        if (Schema::hasTable('quantity_types')) {
            $quantityTypes = [
                'Pcs',
                'Plate',
                'Portion',
                'Kg',
                'Gm',
                'Ltr',
                'Ml',
                'Box',
                'Bottle',
            ];

            foreach ($quantityTypes as $qt) {
                DB::table('quantity_types')->updateOrInsert(
                    [
                        'quantity_type' => $qt,
                        'branch_id' => $branchId,
                    ],
                    [
                        'created_by' => $adminUserId,
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 9. Table Master (Skipped per client requirements)

        // 10. Bank Master
        if (Schema::hasTable('bank_master')) {
            $banks = [
                [
                    'bank_name' => 'SBI Bank',
                    'account_number' => '342234234',
                    'ifsc_code' => 'IC123452101',
                    'branch_name' => 'Adajan',
                    'opening_balance' => 0.00,
                    'status' => 'active',
                ],
                [
                    'bank_name' => 'Cash Account',
                    'account_number' => 'CASH-MAIN',
                    'ifsc_code' => 'N/A',
                    'branch_name' => 'Main Branch',
                    'opening_balance' => 0.00,
                    'status' => 'active',
                ],
            ];

            foreach ($banks as $b) {
                DB::table('bank_master')->updateOrInsert(
                    [
                        'bank_name' => $b['bank_name'],
                        'account_number' => $b['account_number'],
                        'branch_id' => $branchId,
                    ],
                    [
                        'ifsc_code' => $b['ifsc_code'],
                        'branch_name' => $b['branch_name'],
                        'opening_balance' => $b['opening_balance'],
                        'status' => $b['status'],
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 11. Expense Types Master (Skipped per client requirements)

        // 12. Credit Note Types Master
        if (Schema::hasTable('credit_notes_type')) {
            $creditNoteTypes = [
                'Sales Return',
                'Rate Difference',
                'Discount / Rebate',
                'Order Cancellation',
                'Damaged / Spoiled Items',
            ];

            foreach ($creditNoteTypes as $cnt) {
                DB::table('credit_notes_type')->updateOrInsert(
                    [
                        'type_name' => $cnt,
                        'branch_id' => $branchId,
                    ],
                    [
                        'isdeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 13. SMTP Settings Master
        if (Schema::hasTable('smtp_settings')) {
            DB::table('smtp_settings')->updateOrInsert(
                ['branch_id' => $branchId],
                [
                    'mailer' => 'smtp',
                    'host' => env('MAIL_HOST', 'mailpit'),
                    'port' => (string) env('MAIL_PORT', '1025'),
                    'username' => env('MAIL_USERNAME', ''),
                    'password' => env('MAIL_PASSWORD', ''),
                    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                    'from_address' => env('MAIL_FROM_ADDRESS', 'fabinfo@gmail.com'),
                    'from_name' => env('APP_NAME', 'Fablead ERP'),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 14. Discounts Master
        if (Schema::hasTable('discounts')) {
            $discounts = [
                [
                    'name' => 'Welcome Offer 10%',
                    'description' => '10% discount on orders above ₹500',
                    'discount_type' => 'percentage',
                    'discount_value' => 10.00,
                    'min_order_value' => 500.00,
                    'valid_from' => '2026-01-01',
                    'valid_to' => '2026-12-31',
                    'status' => 'active',
                ],
                [
                    'name' => 'Flat ₹100 Off',
                    'description' => 'Flat ₹100 discount on orders above ₹1000',
                    'discount_type' => 'fixed',
                    'discount_value' => 100.00,
                    'min_order_value' => 1000.00,
                    'valid_from' => '2026-01-01',
                    'valid_to' => '2026-12-31',
                    'status' => 'active',
                ],
            ];

            foreach ($discounts as $d) {
                DB::table('discounts')->updateOrInsert(
                    ['name' => $d['name']],
                    [
                        'description' => $d['description'],
                        'discount_type' => $d['discount_type'],
                        'discount_value' => $d['discount_value'],
                        'min_order_value' => $d['min_order_value'],
                        'valid_from' => $d['valid_from'],
                        'valid_to' => $d['valid_to'],
                        'status' => $d['status'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
