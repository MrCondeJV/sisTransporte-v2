<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_can_login_to_admin_panel_with_admin_sistransporte(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@sistransporte.com',
                'password' => 'admin123456',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated('web');
        $this->assertEquals('admin@sistransporte.com', auth()->user()->email);
    }

    public function test_can_login_to_admin_panel_with_admin_empresa(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@empresa.com',
                'password' => 'admin123456',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated('web');
        $this->assertEquals('admin@empresa.com', auth()->user()->email);
    }

    public function test_can_login_to_app_panel_with_admin_empresa(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            ['tenancy_db_name' => 'sistransporte_tenant_empresa1']
        );

        Filament::setCurrentPanel(Filament::getPanel('app'));

        $response = $this->get('/app/login');
        $response->assertSuccessful();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@empresa.com',
                'password' => 'admin123456',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated('web');
        $this->assertEquals('admin@empresa.com', auth()->user()->email);
    }

    public function test_can_login_to_app_panel_with_admin_sistransporte(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'empresa1'],
            ['tenancy_db_name' => 'sistransporte_tenant_empresa1']
        );

        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@sistransporte.com',
                'password' => 'admin123456',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated('web');
        $this->assertEquals('admin@sistransporte.com', auth()->user()->email);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@sistransporte.com',
                'password' => 'wrongpassword',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
    }
}
