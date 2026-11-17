<?php

namespace App\Services\Products;

use App\Models\PublicSettings\ActivityLog;
use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Repositories\Products\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly ProductCodeService $codes,
    ) {}

    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->products->paginateFiltered($search);
    }

    public function categories(): Collection
    {
        return $this->categories->orderedByName();
    }

    public function active(): Collection
    {
        return $this->products->activeOrdered();
    }

    public function create(array $data): Product
    {
        $product = DB::transaction(function () use ($data) {
            $product = $this->products->create($data);

            return $this->codes->assign($product);
        });
        ActivityLog::record('products', 'create', "إضافة منتج {$product->name}", $product);

        return $product;
    }

    public function addSerial(Product $product, string $serial, ?string $batch): ProductSerial
    {
        $serial = trim($serial);
        if ($serial === '') {
            throw ValidationException::withMessages(['serial' => 'السيريال مطلوب.']);
        }
        if (ProductSerial::query()->where('serial', $serial)->exists()) {
            throw ValidationException::withMessages(['serial' => 'هذا السيريال مسجل من قبل.']);
        }

        return $product->serials()->create([
            'serial' => $serial,
            'batch_no' => $batch !== null && trim($batch) !== '' ? trim($batch) : null,
            'status' => 'in_stock',
        ]);
    }

    public function update(Product $product, array $data): Product
    {
        $product = $this->products->update($product, $data);
        ActivityLog::record('products', 'update', "تعديل منتج {$product->name}", $product);

        return $product;
    }

    public function delete(Product $product): ?string
    {
        if ($product->isLinked()) {
            return 'لا يمكن حذف المنتج لأنه مرتبط بفواتير أو حركات مخزون.';
        }

        try {
            $name = $product->name;
            $this->products->delete($product);
            ActivityLog::record('products', 'delete', "حذف منتج {$name}");
        } catch (QueryException) {
            return 'لا يمكن حذف المنتج لأنه مرتبط ببيانات أخرى في النظام.';
        }

        return null;
    }
}
