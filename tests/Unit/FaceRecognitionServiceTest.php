<?php

namespace Tests\Unit;

use App\Services\FaceRecognitionService;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;

class FaceRecognitionServiceTest extends TestCase
{
    protected FaceRecognitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FaceRecognitionService();
    }

    /**
     * Test normalization of valid and invalid descriptors.
     */
    public function test_normalize_descriptor(): void
    {
        // 1. Valid 128-element array
        $valid = array_fill(0, 128, 0.1);
        $result = $this->service->normalizeDescriptor($valid);
        $this->assertNotNull($result);
        $this->assertCount(128, $result);
        $this->assertEquals(0.1, $result[0]);

        // 2. Invalid count (127 elements)
        $invalidCount = array_fill(0, 127, 0.1);
        $this->assertNull($this->service->normalizeDescriptor($invalidCount));

        // 3. Contains non-finite/non-numeric values
        $nonFinite = array_fill(0, 128, 0.1);
        $nonFinite[5] = INF;
        $this->assertNull($this->service->normalizeDescriptor($nonFinite));
    }

    /**
     * Test resolving login matches under different scenarios.
     */
    public function test_resolve_login_match(): void
    {
        // Create dummy users (mock objects instead of DB hits in basic Unit tests)
        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'Alice';
        // stored descriptor: vector filled with 0.1
        $user1->face_descriptor = array_fill(0, 128, 0.1);

        $user2 = new User();
        $user2->id = 2;
        $user2->name = 'Bob';
        // stored descriptor: vector filled with 0.9 (far from 0.1)
        $user2->face_descriptor = array_fill(0, 128, 0.9);

        $candidates = new Collection([$user1, $user2]);

        // Scenario 1: Exact match with Alice
        $searchDescriptor = array_fill(0, 128, 0.1);
        $result = $this->service->resolveLoginMatch($searchDescriptor, $candidates);

        $this->assertEquals('matched', $result['reason']);
        $this->assertNotNull($result['match']);
        $this->assertEquals(1, $result['match']['user']->id);
        $this->assertEquals(0.0, $result['match']['distance']);
        $this->assertEquals(100.0, $result['match']['confidence']);

        // Scenario 2: No match (distance exceeds threshold)
        $farDescriptor = array_fill(0, 128, 0.5); // distance will be sqrt(128 * 0.4^2) = sqrt(20.48) ~= 4.5
        $result = $this->service->resolveLoginMatch($farDescriptor, $candidates);
        $this->assertEquals('no_match', $result['reason']);

        // Scenario 3: Ambiguous match (two candidates are very close to each other and to the input)
        $user3 = new User();
        $user3->id = 3;
        $user3->name = 'Charlie';
        // stored descriptor: vector filled with 0.12 (extremely close to 0.1)
        $user3->face_descriptor = array_fill(0, 128, 0.12);

        $candidatesAmbiguous = new Collection([$user1, $user3]);
        $searchAmbiguous = array_fill(0, 128, 0.1); // input is 0.1. Distance to Alice = 0. Distance to Charlie = sqrt(128 * 0.02^2) = 0.226.
        // Difference is 0.226 - 0 = 0.226, which is > 0.1 gap. So not ambiguous yet.
        
        // Let's make Charlie even closer, e.g. 0.105. Distance to Charlie = sqrt(128 * 0.005^2) = 0.056.
        // Difference = 0.056 - 0 = 0.056 < 0.1 gap. This should trigger ambiguity!
        $user3->face_descriptor = array_fill(0, 128, 0.105);

        $resultAmbiguous = $this->service->resolveLoginMatch($searchAmbiguous, $candidatesAmbiguous);
        $this->assertEquals('ambiguous_match', $resultAmbiguous['reason']);
    }
}
