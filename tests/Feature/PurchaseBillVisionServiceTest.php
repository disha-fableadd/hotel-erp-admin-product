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
}
