<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use App\Models\User;
use Filament\Widgets\Widget;
use Stancl\Tenancy\Database\Models\Domain;

class AdminSaaSOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-saas-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    public function getSaaSMetrics(): array
    {
        $totalTenants = Tenant::count();
        $totalDomains = Domain::count();
        $totalUsers = User::count();

        return [
            'totalTenants' => $totalTenants,
            'totalDomains' => $totalDomains,
            'totalUsers' => $totalUsers,
        ];
    }
}
