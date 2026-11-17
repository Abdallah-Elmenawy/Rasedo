<?php

namespace App\Repositories\Reports\Contracts;

interface DashboardRepositoryInterface
{
    public function snapshot(): array;
}
