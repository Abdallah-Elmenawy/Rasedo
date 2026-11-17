<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->reports->period($request->from, $request->to);

        return view('admin.reports.table', $this->reports->sales($from, $to));
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->reports->period($request->from, $request->to);

        return view('admin.reports.table', $this->reports->purchases($from, $to));
    }

    public function inventory()
    {
        return view('admin.reports.table', $this->reports->inventory());
    }

    public function customers()
    {
        return view('admin.reports.table', $this->reports->customers());
    }

    public function suppliers()
    {
        return view('admin.reports.table', $this->reports->suppliers());
    }

    public function expenses(Request $request)
    {
        [$from, $to] = $this->reports->period($request->from, $request->to);

        return view('admin.reports.table', $this->reports->expenses($from, $to));
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->reports->period($request->from, $request->to);

        return view('admin.reports.profit', $this->reports->profit($from, $to));
    }

    public function financial()
    {
        return view('admin.reports.financial', $this->reports->financial());
    }

    public function accountLedger(Request $request)
    {
        return view('admin.reports.ledger', $this->reports->ledger($request->account_id));
    }
}
