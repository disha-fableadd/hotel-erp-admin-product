<?php

namespace Tests\Feature;

use App\Models\AiPurchaseBill;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\QuantityType;
use App\Models\User;
use App\Services\Purchase\SaveAiPurchaseBill;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveAiPurchaseBillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isolated database: these tests never migrate or write the local hotel DB.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['users', 'categories', 'quantity_types', 'products', 'purchase_invoice', 'purchases', 'product_inventory'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id(); $table->timestamps();
                foreach (['branch_id', 'created_by', 'create_by', 'vendor_id', 'category_id', 'quantity_type_id', 'invoice_id', 'product_id'] as $column) $table->unsignedBigInteger($column)->nullable();
                foreach (['name', 'role', 'phone', 'quantity_type', 'SKU', 'barcode', 'status', 'availablility', 'hsn_code', 'invoice_number', 'bill_no', 'products', 'taxes', 'gst_option', 'item', 'purchase_status', 'payment_status', 'type', 'date'] as $column) $table->text($column)->nullable();
                foreach (['quantity', 'price', 'purchase_price', 'total_amount', 'discount', 'shipping', 'grand_total', 'paid', 'remaining_amount', 'amount_total', 'initial_stock', 'current_stock'] as $column) $table->decimal($column, 14, 4)->default(0);
                $table->boolean('isDeleted')->default(0);
            });
        }
        Schema::create('ai_purchase_bills', function (Blueprint $table) {
            $table->id(); $table->timestamps();
            $table->unsignedBigInteger('branch_id'); $table->unsignedBigInteger('created_by');
            $table->uuid('draft_token')->unique(); $table->text('file_path'); $table->text('original_name');
            $table->json('extraction'); $table->json('reviewed_data')->nullable();
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
        });
        User::forceCreate(['id' => 1, 'name' => 'Hotel admin', 'role' => 'admin']);
    }

    private function draft(string $billNo = 'TEST-1', array $items = []): array
    {
        $token = (string) Str::uuid();
        AiPurchaseBill::create(['branch_id' => 1, 'created_by' => 1, 'draft_token' => $token,
            'file_path' => 'test.jpg', 'original_name' => 'test.jpg', 'extraction' => ['untouched' => 'original']]);
        return ['draft_token' => $token, 'status' => 'completed', 'header' => [
            'bill_no' => $billNo, 'bill_date' => '2026-10-30', 'vendor_name' => 'Hotel supplier',
            'document_type' => 'purchase', 'currency' => 'INR', 'grand_total' => 118,
            'extra_fields' => ['GST' => 18, 'Unfamiliar reference' => 'ABC-42'],
        ], 'items' => $items ?: [[
            'name' => 'Basmati Rice 5kg', 'unit' => 'kg', 'quantity' => 0.5,
            'rate' => 200, 'amount' => 100, 'track_stock' => true, 'category' => 'Kitchen',
            'extra_fields' => ['Batch' => 'LOT-9', 'Expiry' => '2027-01-01'],
        ]]];
    }

    public function test_new_products_fractional_stock_and_all_bill_details_are_saved(): void
    {
        $data = $this->draft();
        $invoice = app(SaveAiPurchaseBill::class)->save($data, 1, 1);
        $this->assertSame(1, Product::count());
        $this->assertEquals(0.5, Product::first()->quantity);
        $this->assertEquals(118, $invoice->grand_total);
        $review = AiPurchaseBill::first()->reviewed_data;
        $this->assertSame('ABC-42', $review['header']['extra_fields']['Unfamiliar reference']);
        $this->assertSame('LOT-9', $review['items'][0]['extra_fields']['Batch']);
        $this->assertEquals(0.5, DB::table('purchases')->value('quantity'));
        $this->assertEquals(0.5, DB::table('product_inventory')->value('current_stock'));
    }

    public function test_saving_same_draft_twice_does_not_repeat_stock_or_invoice(): void
    {
        $data = $this->draft();
        $service = app(SaveAiPurchaseBill::class);
        $one = $service->save($data, 1, 1);
        $two = $service->save($data, 1, 1);
        $this->assertSame($one->id, $two->id);
        $this->assertSame(1, PurchaseInvoice::count());
        $this->assertEquals(0.5, Product::first()->quantity);
    }

    public function test_existing_normalized_name_is_reused_and_stock_increases(): void
    {
        $unit = QuantityType::create(['quantity_type' => 'kg', 'branch_id' => 1]);
        $product = Product::forceCreate(['name' => 'BASMATI  Rice 5kg', 'quantity' => 4.25, 'branch_id' => 1, 'quantity_type_id' => $unit->id, 'price' => 300]);
        app(SaveAiPurchaseBill::class)->save($this->draft(), 1, 1);
        $this->assertSame(1, Product::count());
        $this->assertEquals(4.75, $product->fresh()->quantity);
        $this->assertEquals(300, $product->fresh()->price);
    }

    public function test_services_are_recorded_without_stock(): void
    {
        $data = $this->draft();
        $data['items'][0]['name'] = 'AC maintenance';
        $data['items'][0]['track_stock'] = false;
        app(SaveAiPurchaseBill::class)->save($data, 1, 1);
        $this->assertSame('AC maintenance', Product::first()->name);
        $this->assertEquals(0, Product::first()->quantity);
        $this->assertSame(0, DB::table('product_inventory')->count());
        $this->assertSame(1, DB::table('purchases')->count());
    }

    public function test_reuploaded_vendor_bill_is_rejected_without_stock_changes(): void
    {
        $service = app(SaveAiPurchaseBill::class);
        $service->save($this->draft(), 1, 1);
        try {
            $service->save($this->draft(), 1, 1);
            $this->fail('Duplicate vendor bill should fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('header.bill_no', $e->errors());
        }
        $this->assertSame(1, PurchaseInvoice::count());
        $this->assertEquals(0.5, Product::first()->quantity);
    }

    public function test_mismatched_units_roll_back_the_entire_bill(): void
    {
        $unit = QuantityType::create(['quantity_type' => 'pack', 'branch_id' => 1]);
        $product = Product::forceCreate(['name' => 'Basmati Rice 5kg', 'quantity' => 4, 'branch_id' => 1, 'quantity_type_id' => $unit->id]);
        $data = $this->draft();
        $data['items'][0]['product_id'] = $product->id;
        try {
            app(SaveAiPurchaseBill::class)->save($data, 1, 1);
            $this->fail('Unit mismatch should fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('items.0.unit', $e->errors());
        }
        $this->assertEquals(4, $product->fresh()->quantity);
        $this->assertSame(0, PurchaseInvoice::count());
        $this->assertSame(0, User::where('role', 'vendor')->count());
    }

    public function test_http_save_keeps_unknown_fields_and_bill_level_taxes(): void
    {
        $data = $this->draft();
        $data['header']['extra_fields']['taxes'] = [['name' => 'GST', 'rate' => 18, 'amount' => 18]];
        $this->withoutMiddleware()->actingAs(User::find(1))
            ->postJson(route('purchase.ai-bill.save'), $data)->assertOk()->assertJsonPath('success', true);
        $review = AiPurchaseBill::first()->reviewed_data;
        $this->assertSame('ABC-42', $review['header']['extra_fields']['Unfamiliar reference']);
        $this->assertSame('LOT-9', $review['items'][0]['extra_fields']['Batch']);
        $this->assertEquals(18, json_decode(PurchaseInvoice::first()->taxes, true)[0]['amount']);
        $this->assertSame('with_gst', PurchaseInvoice::first()->gst_option);
    }

    public function test_product_from_another_branch_cannot_be_selected(): void
    {
        $product = Product::forceCreate(['name' => 'Other hotel rice', 'quantity' => 20, 'branch_id' => 2]);
        $data = $this->draft();
        $data['items'][0]['product_id'] = $product->id;
        try {
            app(SaveAiPurchaseBill::class)->save($data, 1, 1);
            $this->fail('Cross branch product should be rejected');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('items.0.product_id', $e->errors());
        }
        $this->assertEquals(20, $product->fresh()->quantity);
        $this->assertSame(0, PurchaseInvoice::count());
    }

    public function test_repeated_product_lines_in_one_bill_reuse_the_catalog_entry(): void
    {
        $data = $this->draft();
        $data['items'][] = $data['items'][0];
        app(SaveAiPurchaseBill::class)->save($data, 1, 1);
        $this->assertSame(1, Product::count());
        $this->assertEquals(1, Product::first()->quantity);
        $this->assertSame(2, DB::table('purchases')->count());
    }
}
