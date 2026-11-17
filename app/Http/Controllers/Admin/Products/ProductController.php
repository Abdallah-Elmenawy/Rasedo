<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\StoreProductRequest;
use App\Http\Requests\Admin\Products\UpdateProductRequest;
use App\Models\Inventory\InventoryMovement;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductSerial;
use App\Services\Products\ProductCodeService;
use App\Services\Products\ProductService;
use App\Support\GridQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly ProductCodeService $codes,
    ) {}

    public function index(Request $request)
    {
        return view('admin.products.index', [
            'products' => $this->products->paginate($request->q),
            'categories' => $this->products->categories(),
            'filters' => [
                'names' => GridQuery::distinct(Product::query(), 'name'),
                'categories' => GridQuery::related(Product::query(), 'category_id', ProductCategory::class),
                'types' => GridQuery::labeled(Product::query(), 'type', ['product' => 'منتج', 'service' => 'خدمة']),
                'statuses' => $this->stockStatuses(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.products.create', [
            'product' => new Product,
            'categories' => $this->products->categories(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->products->create($request->productData());

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', [
            'product' => $product->load('category'),
        ]);
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product' => $product->load('serials'),
            'categories' => $this->products->categories(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->products->update($product, $request->productData());

        return redirect()->route('products.index')->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product)
    {
        $error = $this->products->delete($product);
        if ($error) {
            return back()->with('error', $error);
        }

        return redirect()->route('products.index')->with('success', 'تم حذف المنتج.');
    }

    public function scan(Request $request)
    {
        if (! can('pos.create') && ! can('products.view') && ! can('inventory.view') && ! can('sales.create') && ! can('purchases.create')) {
            abort(403);
        }

        $context = $request->query('context') === 'purchase' ? 'purchase' : 'sale';
        try {
            $found = $this->codes->lookup((string) $request->query('code', ''), $context);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'mode' => $found['mode'],
            'code' => $found['code'] ?? null,
            'kind' => $found['kind'],
            'pieces' => $found['pieces'],
            'product' => $found['product'],
        ]);
    }

    public function barcode(Product $product)
    {
        return response($this->codes->png((string) $product->scan_code, 'barcode'), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function qr(Product $product)
    {
        return response($this->codes->png((string) $product->scan_code, 'qr'), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function label(Product $product)
    {
        return view('admin.products.label', ['product' => $product]);
    }

    public function storeSerial(Request $request, Product $product)
    {
        $data = $request->validate([
            'serial' => ['required', 'string', 'max:80'],
            'batch_no' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $this->products->addSerial($product, $data['serial'], $data['batch_no'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'تم إضافة السيريال.');
    }

    public function destroySerial(Product $product, ProductSerial $serial)
    {
        if ($serial->product_id !== $product->id || $serial->status !== 'in_stock') {
            return back()->with('error', 'لا يمكن حذف هذا السيريال.');
        }
        $serial->delete();

        return back()->with('success', 'تم حذف السيريال.');
    }

    private function stockStatuses(): array
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

        return $statuses;
    }
}
