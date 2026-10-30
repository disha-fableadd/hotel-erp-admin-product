<?php

namespace Tests\Feature;

use App\Services\Purchase\PurchaseBillVisionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PurchaseBillVisionServiceTest extends TestCase
{
    public function test_bill_is_read_after_a_model_is_unavailable(): void
    {
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'test-model']);
        Http::preventStrayRequests();
        Http::fakeSequence()
            ->push(['error' => ['message' => 'Model not found']], 404)
            ->push(['candidates' => [['content' => ['parts' => [['text' => json_encode([
                'vendor_name' => 'Test vendor',
                'items' => [['name' => 'Rice', 'quantity' => 2, 'rate' => 50, 'amount' => 100]],
            ])]]]]]]);

        $result = app(PurchaseBillVisionService::class)->extract(UploadedFile::fake()->image('bill.jpg'));

        $this->assertSame('Test vendor', $result['vendor_name']);
        $this->assertSame('Rice', $result['items'][0]['name']);
        Http::assertSentCount(2);
    }

    public function test_expired_network_budget_stops_before_another_request(): void
    {
        Http::preventStrayRequests();
        $method = new \ReflectionMethod(PurchaseBillVisionService::class, 'postGemini');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reading the bill took too long');
        $method->invoke(app(PurchaseBillVisionService::class), 'test-model', [], microtime(true) - 1);
    }

    public function test_hotel_details_unknown_columns_and_original_dates_are_preserved(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode([
            'vendor_name' => 'Hotel supplier', 'bill_date' => '14/08/2018',
            'GSTIN' => 'TEST-GST', 'extra_fields' => ['Delivery note' => 'DN-42'],
            'items' => [
                ['name' => 'Liquid soap', 'quantity' => 0.125, 'unit' => 'litre', 'amount' => 25, 'Batch' => 'B-1'],
                ['name' => 'AC repair', 'quantity' => 1, 'unit' => 'job', 'track_stock' => false, 'amount' => 100, 'extra_fields' => ['Warranty' => '30 days']],
            ],
        ])]]]]]])]);
        $result = app(PurchaseBillVisionService::class)->extract(UploadedFile::fake()->image('bill.jpg'));
        $this->assertSame('2018-08-14', $result['bill_date']);
        $this->assertSame('TEST-GST', $result['extra_fields']['GSTIN']);
        $this->assertSame('DN-42', $result['extra_fields']['Delivery note']);
        $this->assertSame(0.125, $result['items'][0]['quantity']);
        $this->assertSame('B-1', $result['items'][0]['extra_fields']['Batch']);
        $this->assertFalse($result['items'][1]['track_stock']);
        $this->assertSame('30 days', $result['items'][1]['extra_fields']['Warranty']);
    }
}
