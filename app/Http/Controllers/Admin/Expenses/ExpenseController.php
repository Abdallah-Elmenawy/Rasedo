<?php

namespace App\Http\Controllers\Admin\Expenses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Expenses\StoreMoneyCategoryRequest;
use App\Http\Requests\Admin\Expenses\StoreMoneyEntryRequest;
use App\Models\Expenses\MoneyEntry;
use App\Services\Expenses\ExpenseService;
use RuntimeException;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $expenses) {}

    public function index()
    {
        return view('admin.expenses.index', $this->expenses->page());
    }

    public function show(MoneyEntry $entry)
    {
        return view('admin.expenses.show', [
            'entry' => $entry->load('category', 'treasury', 'creator'),
        ]);
    }

    public function store(StoreMoneyEntryRequest $request)
    {
        try {
            $this->expenses->create($request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل العملية.');
    }

    public function edit(MoneyEntry $entry)
    {
        return view('admin.expenses.edit', $this->expenses->formData($entry));
    }

    public function update(StoreMoneyEntryRequest $request, MoneyEntry $entry)
    {
        try {
            $this->expenses->update($entry, $request->validated());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.index')->with('success', 'تم تحديث العملية.');
    }

    public function destroy(MoneyEntry $entry)
    {
        try {
            $this->expenses->delete($entry);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حذف العملية.');
    }

    public function storeCategory(StoreMoneyCategoryRequest $request)
    {
        $this->expenses->createCategory($request->validated());

        return back()->with('success', 'تم إضافة التصنيف.');
    }
}
