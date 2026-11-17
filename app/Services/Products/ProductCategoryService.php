<?php

namespace App\Services\Products;

use App\Models\Products\ProductCategory;
use App\Repositories\Products\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductCategoryService
{
    public function __construct(private readonly ProductCategoryRepositoryInterface $categories) {}

    public function paginate(): LengthAwarePaginator
    {
        return $this->categories->paginateWithCount();
    }

    public function create(array $data): ProductCategory
    {
        return $this->categories->create($data);
    }

    public function update(ProductCategory $category, array $data): ProductCategory
    {
        return $this->categories->update($category, $data);
    }

    public function delete(ProductCategory $category): void
    {
        $this->categories->delete($category);
    }
}
