<?php

namespace App\Filament\App\Widgets;

use App\Models\Tenant;
use Filament\Widgets\Widget;

class AppDashboardBannerWidget extends Widget
{
    protected string $view = 'filament.app.widgets.app-dashboard-banner';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    public function getTenant(): ?Tenant
    {
        return tenancy()->initialized ? tenant() : Tenant::first();
    }
}
