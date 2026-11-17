<?php

namespace App\Http\Controllers\Admin\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Purchases\StorePurchaseReturnRequest;
use App\Models\Purchases\PurchaseReturn;
use App\Services\Purchases\PurchaseReturnService;
use Illuminate\Http\Request;
use RuntimeException;

class PurchaseReturnController extends Controller
{
    public function __construct(private readonly PurchaseReturnService $returns) {}

    public function index()
    {
        return view('admin.purchase_returns.index', $this->returns->page());
    }

    public function create(Request $request)
    {
        return view('admin.purchase_returns.form', $this->returns->form($request->integer('invoice_id') ?: null));
    }

    public function store(StorePurchaseReturnRequest $request)
    {
        $return = $this->returns->create($request->validated());

        return redirect()->route('purchase-returns.show', $return)->with('success', 'تم حفظ المرتجع.');
    }

    public function edit(PurchaseReturn $purchase_return)
    {
        if ($purchase_return->status !== 'draft') {
            return redirect()->route('purchase-returns.show', $purchase_return)->with('error', 'لا يمكن تعديل مرتجع مؤكد.');
        }

        $purchase_return->load('items.product', 'invoice.supplier', 'invoice.items.product');

        return view('admin.purchase_returns.form', [
            'invoices' => collect(),
            'invoice' => $purchase_return->invoice,
            'return' => $purchase_return,
        ]);
    }

    public function update(StorePurchaseReturnRequest $request, PurchaseReturn $purchase_return)
    {
        try {
            $return = $this->returns->update($purchase_return, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-returns.show', $return)->with('success', 'تم تحديث المرتجع.');
    }

    public function show(PurchaseReturn $purchase_return)
    {
        return view('admin.purchase_returns.show', ['return' => $this->returns->show($purchase_return)]);
    }

    public function destroy(PurchaseReturn $purchase_return)
    {
        try {
            $this->returns->delete($purchase_return);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حذف المرتجع.');
    }

    public function confirm(PurchaseReturn $purchase_return)
    {
        try {
            $this->returns->confirm($purchase_return);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد المرتجع.');
    }
}
