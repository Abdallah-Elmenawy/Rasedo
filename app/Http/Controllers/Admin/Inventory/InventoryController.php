<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreMovementRequest;
use App\Models\Inventory\InventoryMovement;
use App\Models\Products\Product;
use App\Services\Inventory\InventoryService;
use RuntimeException;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index()
    {
        return view('admin.inventory.index', $this->inventory->page());
    }

    public function show(Product $product)
    {
        abort_unless($product->type === 'product', 404);

        return view('admin.inventory.show', [
            'product' => $product,
            'movements' => InventoryMovement::query()
                ->with('creator')
                ->where('product_id', $product->id)
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function store(StoreMovementRequest $request)
    {
        $data = $request->validated();

        try {
            $this->inventory->adjust((int) $data['product_id'], (float) $data['qty'], $data['type'], $data['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل حركة المخزون.');
    }
}
