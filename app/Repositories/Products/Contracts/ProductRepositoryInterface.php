<?php

namespace App\Repositories\Products\Contracts;

use App\Models\Products\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function paginateFiltered(?string $search, int $perPage = 15): LengthAwarePaginator;

    public function activeOrdered(): Collection;

    public function stockProducts(int $perPage = 15): LengthAwarePaginator;

    public function activeStockProducts(): Collection;

    public function activeForPos(): Collection;

    public function findMany(array $ids): Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
