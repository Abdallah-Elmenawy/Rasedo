<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\CheckoutPosRequest;
use App\Models\Treasury\Treasury;
use App\Services\Sales\PosService;
use Illuminate\Http\Request;
use RuntimeException;

class PosController extends Controller
{
    public function __construct(private readonly PosService $pos) {}

    public function index()
    {
        return view('admin.pos.summary', $this->pos->page());
    }

    public function sell()
    {
        return view('admin.pos.sell', $this->pos->page());
    }

    public function openShift(Request $request)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
        ]);
        $treasury = Treasury::query()->where('is_active', true)->find($data['treasury_id']);
        if (! $treasury) {
            return back()->with('error', 'الخزينة غير متاحة.');
        }

        session([
            'pos_shift' => [
                'treasury_id' => $treasury->id,
                'treasury_name' => $treasury->name,
                'opening_balance' => (float) $treasury->current_balance,
                'opened_at' => now()->toDateTimeString(),
                'user_id' => auth()->id(),
            ],
        ]);

        return back()->with('success', 'تم فتح الوردية على خزينة '.$treasury->name.'.');
    }

    public function closeShift(Request $request)
    {
        $shift = session('pos_shift');
        if (! is_array($shift)) {
            return back()->with('error', 'لا توجد وردية مفتوحة.');
        }
        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
        ]);
        $report = $this->pos->closeShift($shift, (float) $data['actual_cash']);
        session()->forget('pos_shift');

        return back()->with('pos_shift_report', $report);
    }

    public function checkout(CheckoutPosRequest $request)
    {
        $shift = session('pos_shift');
        if (! is_array($shift)) {
            return back()->with('error', 'افتح الوردية وحدد الخزينة قبل إصدار الفاتورة.');
        }

        try {
            $invoice = $this->pos->checkout($request->validated(), $shift);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $message = match ($request->input('submit_action')) {
            'draft' => 'تم حفظ فاتورة '.$invoice->number.' كمسودة.',
            'issue' => 'تم إصدار فاتورة '.$invoice->number.'.',
            default => 'تم التحصيل وإصدار إيصال '.$invoice->number.'.',
        };

        return redirect()
            ->route('pos.sell')
            ->with('success', $message)
            ->with('pos_print', route('sales.print', $invoice));
    }
}
