<?php

namespace App\Repositories\Products\Eloquent;

use App\Models\Products\ProductCategory;
use App\Repositories\Products\Contracts\ProductCategoryRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function paginateWithCount(int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductCategory::query()->withCount('products');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
        ]);
        if (request('products_count') !== null && request('products_count') !== '') {
            $query->having('products_count', '=', request('products_count'));
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function orderedByName(): Collection
    {
        return ProductCategory::query()->orderBy('name')->get();
    }

    public function create(array $data): ProductCategory
    {
        return ProductCategory::query()->create($data);
    }

    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(ProductCategory $category): void
    {
        $category->delete();
    }
}
