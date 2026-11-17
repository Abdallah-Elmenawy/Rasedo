<?php

namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Treasury\PaymentRequest;
use App\Http\Requests\Admin\Treasury\ReceiptRequest;
use App\Http\Requests\Admin\Treasury\StoreTreasuryRequest;
use App\Http\Requests\Admin\Treasury\TransferRequest;
use App\Services\Treasury\TreasuryService;
use RuntimeException;

class TreasuryController extends Controller
{
    public function __construct(private readonly TreasuryService $treasury) {}

    public function index()
    {
        return view('admin.treasury.index', $this->treasury->page());
    }

    public function storeTreasury(StoreTreasuryRequest $request)
    {
        $this->treasury->create($request->validated());

        return back()->with('success', 'تم إضافة الخزينة.');
    }

    public function receipt(ReceiptRequest $request)
    {
        try {
            $this->treasury->receipt($request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    public function payment(PaymentRequest $request)
    {
        try {
            $this->treasury->payment($request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الصرف.');
    }

    public function transfer(TransferRequest $request)
    {
        try {
            $this->treasury->transfer($request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم التحويل بين الخزائن.');
    }
}
