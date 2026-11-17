<?php

namespace App\Repositories\Inventory\Eloquent;

use App\Models\Inventory\InventoryMovement;
use App\Repositories\Inventory\Contracts\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function recentMovements(int $limit = 20): Collection
    {
        return InventoryMovement::with(['product', 'creator'])->latest()->limit($limit)->get();
    }

    public function paginateMovements(int $perPage = 15): LengthAwarePaginator
    {
        return InventoryMovement::with(['product', 'creator'])->latest()->paginate($perPage, ['*'], 'moves_page')->withQueryString();
    }
}
