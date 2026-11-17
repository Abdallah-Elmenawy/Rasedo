<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\StoreSalesReturnRequest;
use App\Models\Sales\SalesReturn;
use App\Services\Sales\SalesReturnService;
use Illuminate\Http\Request;
use RuntimeException;

class SalesReturnController extends Controller
{
    public function __construct(private readonly SalesReturnService $returns) {}

    public function index()
    {
        return view('admin.sales_returns.index', $this->returns->page());
    }

    public function create(Request $request)
    {
        return view('admin.sales_returns.form', $this->returns->form($request->integer('invoice_id') ?: null));
    }

    public function store(StoreSalesReturnRequest $request)
    {
        $return = $this->returns->create($request->validated());

        return redirect()->route('sales-returns.show', $return)->with('success', 'تم حفظ المرتجع.');
    }

    public function edit(SalesReturn $sales_return)
    {
        if ($sales_return->status !== 'draft') {
            return redirect()->route('sales-returns.show', $sales_return)->with('error', 'لا يمكن تعديل مرتجع مؤكد.');
        }

        $sales_return->load('items.product', 'invoice.customer', 'invoice.items.product');

        return view('admin.sales_returns.form', [
            'invoices' => collect(),
            'invoice' => $sales_return->invoice,
            'return' => $sales_return,
        ]);
    }

    public function update(StoreSalesReturnRequest $request, SalesReturn $sales_return)
    {
        try {
            $return = $this->returns->update($sales_return, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-returns.show', $return)->with('success', 'تم تحديث المرتجع.');
    }

    public function show(SalesReturn $sales_return)
    {
        return view('admin.sales_returns.show', ['return' => $this->returns->show($sales_return)]);
    }

    public function destroy(SalesReturn $sales_return)
    {
        try {
            $this->returns->delete($sales_return);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حذف المرتجع.');
    }

    public function confirm(SalesReturn $sales_return)
    {
        try {
            $this->returns->confirm($sales_return);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد المرتجع.');
    }
}
