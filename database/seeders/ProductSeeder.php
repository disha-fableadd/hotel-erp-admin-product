<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Seed categories, brands, quantity types, and products with initial inventory.
     */
    public function run(): void
    {
        $branchId = 1;

        $adminId = User::where('role', 'admin')->value('id') ?? 1;
        $vendorId = User::where('role', 'vendor')->value('id') ?? $adminId;

        // 1. Categories
        $categories = [
            'Starters',
            'Main Course',
            'Breads & Rice',
            'Beverages',
            'Desserts',
            'Snacks & Fast Food',
        ];

        $categoryMap = [];
        if (Schema::hasTable('categories')) {
            foreach ($categories as $catName) {
                DB::table('categories')->updateOrInsert(
                    [
                        'name' => $catName,
                        'branch_id' => $branchId,
                    ],
                    [
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $categoryMap[$catName] = DB::table('categories')
                    ->where('name', $catName)
                    ->where('branch_id', $branchId)
                    ->value('id');
            }
        }

        // 2. Brands
        $brands = [
            'In-House Kitchen',
            'Amul',
            'Coca-Cola',
            'Pepsi',
            'Nestle',
            'Mother Dairy',
        ];

        $brandMap = [];
        if (Schema::hasTable('brands')) {
            foreach ($brands as $brandName) {
                DB::table('brands')->updateOrInsert(
                    [
                        'name' => $brandName,
                        'branch_id' => $branchId,
                    ],
                    [
                        'status' => 1,
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $brandMap[$brandName] = DB::table('brands')
                    ->where('name', $brandName)
                    ->where('branch_id', $branchId)
                    ->value('id');
            }
        }

        // 3. Quantity Types (Units)
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

        $quantityTypeMap = [];
        if (Schema::hasTable('quantity_types')) {
            foreach ($quantityTypes as $qt) {
                DB::table('quantity_types')->updateOrInsert(
                    [
                        'quantity_type' => $qt,
                        'branch_id' => $branchId,
                    ],
                    [
                        'created_by' => $adminId,
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $quantityTypeMap[$qt] = DB::table('quantity_types')
                    ->where('quantity_type', $qt)
                    ->where('branch_id', $branchId)
                    ->value('id');
            }
        }

        // 4. Products with Stock & Inventory
        if (Schema::hasTable('products')) {
            $products = [
                // Starters
                [
                    'name' => 'Paneer Tikka',
                    'category' => 'Starters',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-STR-001',
                    'barcode' => 'PRD1000000001',
                    'price' => 240.00,
                    'quantity' => 50,
                    'description' => 'Marinated cottage cheese grilled to perfection in tandoor.',
                ],
                [
                    'name' => 'Veg Crispy',
                    'category' => 'Starters',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-STR-002',
                    'barcode' => 'PRD1000000002',
                    'price' => 190.00,
                    'quantity' => 45,
                    'description' => 'Crispy fried assorted vegetables tossed in sweet and spicy sauce.',
                ],
                [
                    'name' => 'Veg Spring Roll',
                    'category' => 'Starters',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-STR-003',
                    'barcode' => 'PRD1000000003',
                    'price' => 160.00,
                    'quantity' => 40,
                    'description' => 'Golden fried crispy rolls stuffed with seasoned vegetables.',
                ],

                // Main Course
                [
                    'name' => 'Paneer Butter Masala',
                    'category' => 'Main Course',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-MNC-001',
                    'barcode' => 'PRD1000000004',
                    'price' => 260.00,
                    'quantity' => 60,
                    'description' => 'Rich and creamy curry made with cottage cheese, butter, and mild spices.',
                ],
                [
                    'name' => 'Dal Makhani',
                    'category' => 'Main Course',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-MNC-002',
                    'barcode' => 'PRD1000000005',
                    'price' => 210.00,
                    'quantity' => 50,
                    'description' => 'Slow cooked black lentils simmered with cream and butter.',
                ],
                [
                    'name' => 'Veg Kadhai',
                    'category' => 'Main Course',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-MNC-003',
                    'barcode' => 'PRD1000000006',
                    'price' => 230.00,
                    'quantity' => 40,
                    'description' => 'Mixed vegetables cooked in a spicy freshly ground kadhai masala.',
                ],

                // Breads & Rice
                [
                    'name' => 'Butter Naan',
                    'category' => 'Breads & Rice',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Pcs',
                    'SKU' => 'SKU-BRD-001',
                    'barcode' => 'PRD1000000007',
                    'price' => 45.00,
                    'quantity' => 200,
                    'description' => 'Traditional leavened flatbread topped with generous butter.',
                ],
                [
                    'name' => 'Tandoori Roti',
                    'category' => 'Breads & Rice',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Pcs',
                    'SKU' => 'SKU-BRD-002',
                    'barcode' => 'PRD1000000008',
                    'price' => 25.00,
                    'quantity' => 250,
                    'description' => 'Whole wheat flatbread roasted inside the clay tandoor.',
                ],
                [
                    'name' => 'Jeera Rice',
                    'category' => 'Breads & Rice',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Portion',
                    'SKU' => 'SKU-BRD-003',
                    'barcode' => 'PRD1000000009',
                    'price' => 140.00,
                    'quantity' => 60,
                    'description' => 'Fragrant basmati rice tempered with cumin seeds and desi ghee.',
                ],
                [
                    'name' => 'Veg Hyderabadi Biryani',
                    'category' => 'Breads & Rice',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Portion',
                    'SKU' => 'SKU-BRD-004',
                    'barcode' => 'PRD1000000010',
                    'price' => 240.00,
                    'quantity' => 45,
                    'description' => 'Aromatic layered basmati rice with vegetables and saffron spices.',
                ],

                // Beverages
                [
                    'name' => 'Coca-Cola 300ml',
                    'category' => 'Beverages',
                    'brand' => 'Coca-Cola',
                    'unit' => 'Bottle',
                    'SKU' => 'SKU-BEV-001',
                    'barcode' => 'PRD1000000011',
                    'price' => 40.00,
                    'quantity' => 100,
                    'description' => 'Chilled carbonated soft drink 300ml glass bottle.',
                ],
                [
                    'name' => 'Mineral Water 1L',
                    'category' => 'Beverages',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Bottle',
                    'SKU' => 'SKU-BEV-002',
                    'barcode' => 'PRD1000000012',
                    'price' => 20.00,
                    'quantity' => 120,
                    'description' => 'Packaged purified drinking water 1 Litre bottle.',
                ],
                [
                    'name' => 'Fresh Lime Soda',
                    'category' => 'Beverages',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Portion',
                    'SKU' => 'SKU-BEV-003',
                    'barcode' => 'PRD1000000013',
                    'price' => 60.00,
                    'quantity' => 50,
                    'description' => 'Refreshing drink prepared with freshly squeezed lime and sparkling soda.',
                ],
                [
                    'name' => 'Amul Masala Buttermilk',
                    'category' => 'Beverages',
                    'brand' => 'Amul',
                    'unit' => 'Bottle',
                    'SKU' => 'SKU-BEV-004',
                    'barcode' => 'PRD1000000014',
                    'price' => 25.00,
                    'quantity' => 80,
                    'description' => 'Refreshing spiced buttermilk pouch/bottle.',
                ],

                // Desserts
                [
                    'name' => 'Gulab Jamun (2 Pcs)',
                    'category' => 'Desserts',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-DES-001',
                    'barcode' => 'PRD1000000015',
                    'price' => 70.00,
                    'quantity' => 50,
                    'description' => 'Warm soft milk dough dumplings soaked in rose flavored sugar syrup.',
                ],
                [
                    'name' => 'Amul Vanilla Ice Cream',
                    'category' => 'Desserts',
                    'brand' => 'Amul',
                    'unit' => 'Portion',
                    'SKU' => 'SKU-DES-002',
                    'barcode' => 'PRD1000000016',
                    'price' => 60.00,
                    'quantity' => 40,
                    'description' => 'Creamy rich vanilla ice cream cup.',
                ],

                // Snacks & Fast Food
                [
                    'name' => 'Veg Cheese Grilled Sandwich',
                    'category' => 'Snacks & Fast Food',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-SNK-001',
                    'barcode' => 'PRD1000000017',
                    'price' => 120.00,
                    'quantity' => 40,
                    'description' => 'Layered sandwich with spiced veggies and melted cheddar cheese.',
                ],
                [
                    'name' => 'Peri Peri French Fries',
                    'category' => 'Snacks & Fast Food',
                    'brand' => 'In-House Kitchen',
                    'unit' => 'Plate',
                    'SKU' => 'SKU-SNK-002',
                    'barcode' => 'PRD1000000018',
                    'price' => 100.00,
                    'quantity' => 50,
                    'description' => 'Crispy golden potato fries dusted with tangy peri peri spice blend.',
                ],
            ];

            foreach ($products as $p) {
                $catId = $categoryMap[$p['category']] ?? 1;
                $brdId = $brandMap[$p['brand']] ?? 1;
                $qtId = $quantityTypeMap[$p['unit']] ?? 1;

                DB::table('products')->updateOrInsert(
                    [
                        'SKU' => $p['SKU'],
                        'branch_id' => $branchId,
                    ],
                    [
                        'name' => $p['name'],
                        'barcode' => $p['barcode'],
                        'vendor_id' => $vendorId,
                        'category_id' => $catId,
                        'brand_id' => $brdId,
                        'quantity_type_id' => $qtId,
                        'description' => $p['description'],
                        'price' => $p['price'],
                        'quantity' => $p['quantity'],
                        'availablility' => 'in_stock',
                        'status' => 'active',
                        'isDeleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $productId = DB::table('products')
                    ->where('SKU', $p['SKU'])
                    ->where('branch_id', $branchId)
                    ->value('id');

                // Seed Initial Stock in product_inventory
                if ($productId && Schema::hasTable('product_inventory')) {
                    DB::table('product_inventory')->updateOrInsert(
                        [
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'type' => 'Create',
                        ],
                        [
                            'initial_stock' => $p['quantity'],
                            'current_stock' => $p['quantity'],
                            'create_by' => $adminId,
                            'date' => now()->toDateString(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
