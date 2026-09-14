<?php

namespace Tests\Feature;

use Tests\TestCase;

class DomainResolutionTest extends TestCase
{
    public function test_central_domain_redirects_to_admin(): void
    {
        $response = $this->get('http://sistransporte-v2.test/');
        $response->assertRedirect('/admin');
    }

    public function test_central_admin_panel_is_accessible(): void
    {
        $response = $this->get('http://sistransporte-v2.test/admin/login');
        $response->assertStatus(200);
    }

    public function test_tenant_domain_is_recognized_and_redirects_to_app(): void
    {
        $response = $this->get('http://empresa1.sistransporte-v2.test/');
        $response->assertRedirect('/app');
    }

    public function test_tenant_app_panel_is_accessible(): void
    {
        $response = $this->get('http://empresa1.sistransporte-v2.test/app/login');
        $response->assertStatus(200);
    }
}
