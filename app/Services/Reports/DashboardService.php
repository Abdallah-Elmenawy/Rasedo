<?php

namespace App\Services\Reports;

use App\Repositories\Reports\Contracts\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard) {}

    public function page(): array
    {
        return $this->dashboard->snapshot();
    }
}
