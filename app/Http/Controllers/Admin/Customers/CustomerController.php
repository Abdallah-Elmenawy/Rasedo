<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\CollectCustomerRequest;
use App\Http\Requests\Admin\Customers\StoreCustomerRequest;
use App\Http\Requests\Admin\Customers\UpdateCustomerRequest;
use App\Models\Customers\Customer;
use App\Models\Treasury\Treasury;
use App\Services\Customers\CustomerService;
use App\Support\GridQuery;
use Illuminate\Http\Request;
use RuntimeException;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    public function index(Request $request)
    {
        return view('admin.customers.index', [
            'customers' => $this->customers->paginate($request->q, $request->status),
            'filters' => [
                'names' => GridQuery::distinct(Customer::query(), 'name'),
                'creators' => GridQuery::users(Customer::query()),
            ],
        ]);
    }

    public function accounts()
    {
        return view('admin.customers.accounts', [
            'customers' => $this->customers->accounts(),
            'filters' => [
                'names' => GridQuery::distinct(Customer::query(), 'name'),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.customers.create', ['customer' => new Customer]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customers->create($request->validated());

        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل.');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', [
            'customer' => $this->customers->profile($customer),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customers->update($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Customer $customer)
    {
        $this->customers->delete($customer);

        return redirect()->route('customers.index')->with('success', 'تم حذف العميل.');
    }

    public function collect(CollectCustomerRequest $request, Customer $customer)
    {
        try {
            $this->customers->collect($customer, $request->validated());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }
}
