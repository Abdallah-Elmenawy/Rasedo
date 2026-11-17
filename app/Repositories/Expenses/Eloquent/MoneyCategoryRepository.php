<?php

namespace App\Repositories\Expenses\Eloquent;

use App\Models\Expenses\MoneyCategory;
use App\Repositories\Expenses\Contracts\MoneyCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class MoneyCategoryRepository implements MoneyCategoryRepositoryInterface
{
    public function orderedByName(): Collection
    {
        return MoneyCategory::query()->orderBy('name')->get();
    }

    public function create(array $data): MoneyCategory
    {
        return MoneyCategory::query()->create($data);
    }
}
