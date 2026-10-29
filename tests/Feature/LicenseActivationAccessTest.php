<?php

namespace Tests\Feature;

use App\Services\License\LicenseManager;
use Tests\TestCase;

class LicenseActivationAccessTest extends TestCase
{
    public function test_activation_page_is_reachable_without_a_tenant_or_license(): void
    {
        config(['tenant.enabled' => true, 'license.enabled' => true]);

        $this->mock(LicenseManager::class, function ($mock) {
            $mock->shouldReceive('shouldEnforce')->andReturn(true);
            $mock->shouldReceive('isEnforcementEnabled')->andReturn(true);
            $mock->shouldReceive('allowsAccess')->andReturn(false);
            $mock->shouldReceive('status')->andReturn([]);
        });

        $this->get('/license')->assertOk()->assertViewIs('license.activate');
        $this->get('/license/activate')->assertRedirect('/license');
    }
}
