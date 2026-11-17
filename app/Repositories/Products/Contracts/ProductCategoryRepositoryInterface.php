<?php

namespace App\Repositories\Products\Contracts;

use App\Models\Products\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductCategoryRepositoryInterface
{
    public function paginateWithCount(int $perPage = 15): LengthAwarePaginator;

    public function orderedByName(): Collection;

    public function create(array $data): ProductCategory;

    public function update(ProductCategory $category, array $data): ProductCategory;

    public function delete(ProductCategory $category): void;
}
