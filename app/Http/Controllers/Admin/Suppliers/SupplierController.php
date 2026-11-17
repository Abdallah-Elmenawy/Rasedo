<?php

namespace App\Http\Controllers\Admin\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Suppliers\PaySupplierRequest;
use App\Http\Requests\Admin\Suppliers\StoreSupplierRequest;
use App\Http\Requests\Admin\Suppliers\UpdateSupplierRequest;
use App\Models\Suppliers\Supplier;
use App\Models\Treasury\Treasury;
use App\Services\Suppliers\SupplierService;
use App\Support\GridQuery;
use Illuminate\Http\Request;
use RuntimeException;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers) {}

    public function index(Request $request)
    {
        return view('admin.suppliers.index', [
            'suppliers' => $this->suppliers->paginate($request->q, $request->status),
            'filters' => [
                'names' => GridQuery::distinct(Supplier::query(), 'name'),
                'creators' => GridQuery::users(Supplier::query()),
            ],
        ]);
    }

    public function accounts()
    {
        return view('admin.suppliers.accounts', [
            'suppliers' => $this->suppliers->accounts(),
            'filters' => [
                'names' => GridQuery::distinct(Supplier::query(), 'name'),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.suppliers.create', ['supplier' => new Supplier]);
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->suppliers->create($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد.');
    }

    public function show(Supplier $supplier)
    {
        return view('admin.suppliers.show', [
            'supplier' => $this->suppliers->profile($supplier),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->suppliers->update($supplier, $request->validated());

        return redirect()->route('suppliers.index')->with('success', 'تم تحديث بيانات المورد.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->suppliers->delete($supplier);

        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد.');
    }

    public function pay(PaySupplierRequest $request, Supplier $supplier)
    {
        try {
            $this->suppliers->pay($supplier, $request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الدفعة.');
    }
}
