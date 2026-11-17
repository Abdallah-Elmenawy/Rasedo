<?php

namespace App\Repositories\Products\Eloquent;

use App\Models\Products\Product;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginateFiltered(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with('category');
        GridQuery::apply($query, [
            'sku' => ['column' => 'sku', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'category_id' => ['column' => 'category_id', 'type' => 'select'],
            'type' => ['column' => 'type', 'type' => 'select'],
            'cost_price' => ['column' => 'cost_price', 'type' => 'number'],
            'sale_price' => ['column' => 'sale_price', 'type' => 'number'],
            'current_stock' => ['column' => 'current_stock', 'type' => 'number'],
        ]);
        $query->when(request('stock_status') === 'out', fn ($q) => $q->where('type', 'product')->where('current_stock', '<=', 0))
            ->when(request('stock_status') === 'low', fn ($q) => $q->where('type', 'product')->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock'))
            ->when(request('stock_status') === 'ok', fn ($q) => $q->where('type', 'product')->whereColumn('current_stock', '>', 'min_stock'));

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function activeOrdered(): Collection
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function stockProducts(int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with('category')->where('type', 'product');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'current_stock' => ['column' => 'current_stock', 'type' => 'number'],
            'min_stock' => ['column' => 'min_stock', 'type' => 'number'],
        ]);
        if (request()->filled('stock_value')) {
            $query->whereRaw('(current_stock * cost_price) = ?', [request('stock_value')]);
        }
        $query->when(request('stock_status') === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->when(request('stock_status') === 'low', fn ($q) => $q->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock'))
            ->when(request('stock_status') === 'ok', fn ($q) => $q->whereColumn('current_stock', '>', 'min_stock'));

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    public function activeStockProducts(): Collection
    {
        return Product::query()->where('type', 'product')->where('is_active', true)->orderBy('name')->get();
    }

    public function activeForPos(): Collection
    {
        return Product::query()->where('is_active', true)->with('category')->orderBy('name')->get();
    }

    public function findMany(array $ids): Collection
    {
        return Product::query()->whereIn('id', $ids)->get()->keyBy('id');
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
