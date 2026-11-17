<?php

namespace App\Services\Inventory;

use App\Models\Products\Product;
use App\Support\GridQuery;
use App\Repositories\Inventory\Contracts\InventoryRepositoryInterface;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Services\Accounting\AccountingService;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventory,
        private readonly ProductRepositoryInterface $products,
        private readonly AccountingService $accounting,
    ) {}

    public function page(): array
    {
        $stock = Product::query()->where('type', 'product');
        $statuses = [];
        if ((clone $stock)->where('current_stock', '<=', 0)->exists()) {
            $statuses['out'] = 'نفد';
        }
        if ((clone $stock)->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock')->exists()) {
            $statuses['low'] = 'منخفض';
        }
        if ((clone $stock)->whereColumn('current_stock', '>', 'min_stock')->exists()) {
            $statuses['ok'] = 'متوفر';
        }

        return [
            'products' => $this->products->stockProducts(),
            'formProducts' => $this->products->activeStockProducts(),
            'movements' => $this->inventory->paginateMovements(),
            'filters' => [
                'names' => GridQuery::distinct($stock, 'name'),
                'statuses' => $statuses,
            ],
        ];
    }

    public function adjust(int $productId, float $qty, string $type, ?string $notes): void
    {
        $product = $this->products->findMany([$productId])->first()
            ?? Product::query()->findOrFail($productId);

        $this->accounting->adjustStock($product, $qty, $type, $notes);
    }
}
