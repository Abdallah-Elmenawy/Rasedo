<?php

namespace App\Repositories\Expenses\Contracts;

use App\Models\Expenses\MoneyCategory;
use Illuminate\Support\Collection;

interface MoneyCategoryRepositoryInterface
{
    public function orderedByName(): Collection;

    public function create(array $data): MoneyCategory;
}
