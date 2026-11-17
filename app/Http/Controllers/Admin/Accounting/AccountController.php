<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\StoreAccountRequest;
use App\Http\Requests\Admin\Accounting\UpdateAccountRequest;
use App\Models\Accounting\Account;
use App\Services\Accounting\AccountService;
use App\Support\GridQuery;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function index()
    {
        $accounts = $this->accounts->chart();
        $typeLabels = [
            'asset' => 'أصل',
            'liability' => 'التزام',
            'equity' => 'ملكية',
            'revenue' => 'إيراد',
            'expense' => 'مصروف',
        ];

        $filtered = GridQuery::applyCollection($accounts, [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'type' => ['key' => 'type', 'type' => 'select'],
            'parent_id' => ['key' => 'parent_id', 'type' => 'select'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
            'is_active' => ['key' => 'is_active', 'type' => 'bool'],
        ]);

        return view('admin.accounts.index', [
            'accounts' => paginate_collection($filtered),
            'filters' => [
                'names' => $accounts->pluck('name', 'name')->filter()->all(),
                'types' => $accounts->pluck('type')->unique()->filter()->mapWithKeys(fn ($type) => [$type => $typeLabels[$type] ?? $type])->all(),
                'parents' => $accounts->pluck('parent')->filter()->unique('id')->mapWithKeys(fn ($parent) => [$parent->id => $parent->name])->all(),
                'states' => $accounts->pluck('is_active')->unique()->mapWithKeys(fn ($active) => [(int) (bool) $active => $active ? 'نشط' : 'غير نشط'])->all(),
            ],
        ]);
    }

    public function store(StoreAccountRequest $request)
    {
        $this->accounts->create($request->validated());

        return back()->with('success', 'تم إضافة الحساب.');
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $this->accounts->update($account, $request->accountData());

        return back()->with('success', 'تم تحديث الحساب.');
    }

    public function destroy(Account $account)
    {
        try {
            $this->accounts->delete($account);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'تم حذف الحساب.');
    }

    public function receivables()
    {
        $customers = $this->accounts->receivables();

        $filtered = GridQuery::applyCollection($customers, [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'phone' => ['key' => 'phone', 'type' => 'text'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
        ]);

        return view('admin.accounts.receivables', [
            'customers' => paginate_collection($filtered),
            'balanceTotal' => $filtered->sum('current_balance'),
            'filters' => [
                'names' => $customers->pluck('name', 'name')->filter()->all(),
            ],
        ]);
    }

    public function payables()
    {
        $suppliers = $this->accounts->payables();

        $filtered = GridQuery::applyCollection($suppliers, [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'phone' => ['key' => 'phone', 'type' => 'text'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
        ]);

        return view('admin.accounts.payables', [
            'suppliers' => paginate_collection($filtered),
            'balanceTotal' => $filtered->sum('current_balance'),
            'filters' => [
                'names' => $suppliers->pluck('name', 'name')->filter()->all(),
            ],
        ]);
    }
}
