<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;
        $subdomain = !empty($this->data['subdomain']) ? $this->data['subdomain'] : $tenant->id;
        
        $baseHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $domain = $subdomain . '.' . $baseHost;

        $tenant->domains()->create([
            'domain' => $domain,
        ]);
    }
}
