<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Brand;
use App\Models\QuantityType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GlobalCategoryBrandTest extends TestCase
{
    use DatabaseTransactions;

    protected function createBranchAndUser(int $id, string $role, ?int $branchId = null): User
    {
        DB::table('branches')->insertOrIgnore([
            'id' => $id,
            'name' => 'Branch ' . $id,
            'code' => 'BR' . str_pad((string) $id, 3, '0', STR_PAD_LEFT),
            'email' => 'branch' . $id . '@example.com',
            'phone' => '9000000' . $id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insertOrIgnore([
            'id' => $id,
            'name' => 'User ' . $id,
            'email' => 'user' . $id . '@example.com',
            'phone' => '8888888' . $id,
            'password' => bcrypt('password123'),
            'role' => $role,
            'branch_id' => $branchId ?? $id,
            'isDeleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    public function test_categories_and_brands_are_fetched_globally(): void
    {
        $branch1User = $this->createBranchAndUser(901, 'sub-admin');
        $branch2User = $this->createBranchAndUser(902, 'sub-admin');

        // Create category and brand for Branch 1 (simulating legacy data)
        $cat1 = Category::create([
            'name' => 'Branch 1 Category',
            'branch_id' => 901,
            'isDeleted' => 0,
        ]);
        $brand1 = Brand::create([
            'name' => 'Branch 1 Brand',
            'branch_id' => 901,
            'isDeleted' => 0,
        ]);

        // Create global category and brand (branch_id = null)
        $catGlobal = Category::create([
            'name' => 'Global Category',
            'branch_id' => null,
            'isDeleted' => 0,
        ]);
        $brandGlobal = Brand::create([
            'name' => 'Global Brand',
            'branch_id' => null,
            'isDeleted' => 0,
        ]);

        // 1. Authenticate as Branch 2 sub-admin
        Passport::actingAs($branch2User);

        // Fetch categories list - should return both Branch 1 and Global categories
        $response = $this->getJson('/api/getAllCategory');
        $response->assertStatus(200);
        $categories = collect($response->json('data'));
        $this->assertTrue($categories->contains('id', $cat1->id));
        $this->assertTrue($categories->contains('id', $catGlobal->id));

        // Fetch brands list - should return both Branch 1 and Global brands
        $response = $this->getJson('/api/getAllBrand');
        $response->assertStatus(200);
        $brands = collect($response->json('data'));
        $this->assertTrue($brands->contains('id', $brand1->id));
        $this->assertTrue($brands->contains('id', $brandGlobal->id));
    }

    public function test_add_category_and_brand_saves_with_null_branch_id_and_validates_globally(): void
    {
        $admin = $this->createBranchAndUser(903, 'admin');
        Passport::actingAs($admin);

        // Add Category
        $response = $this->postJson('/api/addcategory', [
            'name' => 'Unique Global Category',
        ]);
        $response->assertStatus(200);
        $catId = $response->json('category.id');
        $this->assertDatabaseHas('categories', [
            'id' => $catId,
            'name' => 'Unique Global Category',
            'branch_id' => null,
        ]);

        // Attempting to add duplicate category name should fail validation globally
        $dupResponse = $this->postJson('/api/addcategory', [
            'name' => 'Unique Global Category',
        ]);
        $dupResponse->assertStatus(422);
        $dupResponse->assertJsonValidationErrors(['name']);

        // Add Brand
        $brandResponse = $this->postJson('/api/addBrand', [
            'name' => 'Unique Global Brand',
        ]);
        $brandResponse->assertStatus(200);
        $brandId = $brandResponse->json('brand.id');
        $this->assertDatabaseHas('brands', [
            'id' => $brandId,
            'name' => 'Unique Global Brand',
            'branch_id' => null,
        ]);
    }

    public function test_get_category_and_get_brand_and_get_quantity_type_endpoints_return_global_lists(): void
    {
        $staff = $this->createBranchAndUser(904, 'staff');
        Passport::actingAs($staff);

        // Seed global categories, brands, and quantity types
        $cat = Category::create(['name' => 'Test Cat', 'branch_id' => null, 'isDeleted' => 0]);
        $brand = Brand::create(['name' => 'Test Brand', 'branch_id' => null, 'isDeleted' => 0]);
        $qtyType = QuantityType::create(['quantity_type' => 'Test Qty', 'created_by' => null, 'isDeleted' => 0]);

        // get-category
        $response = $this->getJson('/api/get-category');
        $response->assertStatus(200);
        $categories = collect($response->json('data'));
        $this->assertTrue($categories->contains('id', $cat->id));

        // get-brand
        $response = $this->getJson('/api/get-brand');
        $response->assertStatus(200);
        $brands = collect($response->json('data'));
        $this->assertTrue($brands->contains('id', $brand->id));

        // get-quantity-type
        $response = $this->getJson('/api/get-quantity-type');
        $response->assertStatus(200);
        $types = collect($response->json('data'));
        $this->assertTrue($types->contains('id', $qtyType->id));
    }
}
