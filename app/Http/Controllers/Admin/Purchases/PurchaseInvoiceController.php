<?php

namespace App\Http\Controllers\Admin\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Purchases\PayPurchaseInvoiceRequest;
use App\Http\Requests\Admin\Purchases\StorePurchaseInvoiceRequest;
use App\Http\Requests\Admin\Purchases\UpdatePurchaseAttachmentRequest;
use App\Models\Purchases\PurchaseInvoice;
use App\Services\Purchases\PurchaseInvoiceService;
use Illuminate\Http\Request;
use RuntimeException;

class PurchaseInvoiceController extends Controller
{
    public function __construct(private readonly PurchaseInvoiceService $invoices) {}

    public function index(Request $request)
    {
        return view('admin.purchases.index', $this->invoices->index($request->q, $request->supplier_id, $request->status));
    }

    public function create()
    {
        return view('admin.purchases.create', $this->invoices->editForm(new PurchaseInvoice([
            'invoice_date' => now()->toDateString(),
        ])));
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        $invoice = $this->invoices->save($request->validated());
        if ($request->boolean('confirm')) {
            try {
                $this->invoices->confirmAndPay($invoice, $request->only(['payment_status', 'paid_amount', 'treasury_id']));
            } catch (RuntimeException $e) {
                return redirect()->route('purchases.edit', $invoice)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('purchases.show', $invoice->fresh())->with('success', 'تم حفظ فاتورة المشتريات.');
    }

    public function show(PurchaseInvoice $purchase)
    {
        return view('admin.purchases.show', $this->invoices->show($purchase));
    }

    public function edit(PurchaseInvoice $purchase)
    {
        if (! $purchase->isEditable()) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }

        return view('admin.purchases.edit', $this->invoices->editForm($purchase));
    }

    public function update(StorePurchaseInvoiceRequest $request, PurchaseInvoice $purchase)
    {
        if (! $purchase->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }
        $invoice = $this->invoices->save($request->validated(), $purchase);
        if ($request->boolean('confirm')) {
            try {
                $this->invoices->confirmAndPay($invoice, $request->only(['payment_status', 'paid_amount', 'treasury_id']));
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return redirect()->route('purchases.show', $invoice->fresh())->with('success', 'تم تحديث الفاتورة.');
    }

    public function confirm(PurchaseInvoice $purchase)
    {
        try {
            $this->invoices->confirm($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد الفاتورة.');
    }

    public function attachment(UpdatePurchaseAttachmentRequest $request, PurchaseInvoice $purchase)
    {
        if ($request->hasFile('attachment')) {
            $this->invoices->storeAttachment($purchase, $request->file('attachment'));
        } else {
            $this->invoices->clearAttachment($purchase);
        }

        return back()->with('success', 'تم تحديث مرفق الفاتورة.');
    }

    public function pay(PayPurchaseInvoiceRequest $request, PurchaseInvoice $purchase)
    {
        try {
            $this->invoices->pay($purchase, $request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الصرف.');
    }

    public function print(PurchaseInvoice $purchase)
    {
        $invoice = $this->invoices->print($purchase);

        return view('invoices.print.frame', [
            'invoice' => $invoice,
            'party' => $invoice->supplier,
            'partyLabel' => 'المورد',
            'codeLabel' => 'كود المورد',
            'backUrl' => route('purchases.show', $invoice),
        ]);
    }

    public function cancel(PurchaseInvoice $purchase)
    {
        try {
            $this->invoices->cancel($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إلغاء الفاتورة.');
    }

    public function destroy(PurchaseInvoice $purchase)
    {
        try {
            $this->invoices->delete($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.index')->with('success', 'تم حذف الفاتورة.');
    }
}
