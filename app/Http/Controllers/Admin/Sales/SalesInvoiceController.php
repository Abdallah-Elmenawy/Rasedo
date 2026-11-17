<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\CollectSalesInvoiceRequest;
use App\Http\Requests\Admin\Sales\StoreSalesInvoiceRequest;
use App\Models\Sales\SalesInvoice;
use App\Services\Sales\SalesInvoiceService;
use Illuminate\Http\Request;
use RuntimeException;

class SalesInvoiceController extends Controller
{
    public function __construct(private readonly SalesInvoiceService $invoices) {}

    public function index(Request $request)
    {
        return view('admin.sales.index', $this->invoices->index($request->q, $request->customer_id, $request->status));
    }

    public function create()
    {
        return view('admin.sales.create', $this->invoices->editForm(new SalesInvoice([
            'invoice_date' => now()->toDateString(),
        ])));
    }

    public function store(StoreSalesInvoiceRequest $request)
    {
        $invoice = $this->invoices->save($request->validated());
        if ($request->boolean('confirm')) {
            try {
                $this->invoices->confirmAndCollect($invoice, $request->only(['payment_status', 'paid_amount', 'treasury_id']));
            } catch (RuntimeException $e) {
                return redirect()->route('sales.edit', $invoice)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('sales.show', $invoice->fresh())->with('success', 'تم حفظ فاتورة المبيعات.');
    }

    public function show(SalesInvoice $sale)
    {
        return view('admin.sales.show', $this->invoices->show($sale));
    }

    public function edit(SalesInvoice $sale)
    {
        if (! $sale->isEditable()) {
            return redirect()->route('sales.show', $sale)->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }

        return view('admin.sales.edit', $this->invoices->editForm($sale));
    }

    public function update(StoreSalesInvoiceRequest $request, SalesInvoice $sale)
    {
        if (! $sale->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }
        $invoice = $this->invoices->save($request->validated(), $sale);
        if ($request->boolean('confirm')) {
            try {
                $this->invoices->confirmAndCollect($invoice, $request->only(['payment_status', 'paid_amount', 'treasury_id']));
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return redirect()->route('sales.show', $invoice->fresh())->with('success', 'تم تحديث الفاتورة.');
    }

    public function confirm(SalesInvoice $sale)
    {
        try {
            $this->invoices->confirm($sale);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد الفاتورة.');
    }

    public function pay(CollectSalesInvoiceRequest $request, SalesInvoice $sale)
    {
        try {
            $this->invoices->collect($sale, $request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    public function print(SalesInvoice $sale)
    {
        $invoice = $this->invoices->print($sale);

        return view('invoices.print.frame', [
            'invoice' => $invoice,
            'party' => $invoice->customer,
            'partyLabel' => 'فاتورة إلى',
            'codeLabel' => 'كود العميل',
            'backUrl' => route('sales.show', $invoice),
        ]);
    }

    public function cancel(SalesInvoice $sale)
    {
        try {
            $this->invoices->cancel($sale);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إلغاء الفاتورة.');
    }

    public function destroy(SalesInvoice $sale)
    {
        try {
            $this->invoices->delete($sale);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'تم حذف الفاتورة.');
    }
}
