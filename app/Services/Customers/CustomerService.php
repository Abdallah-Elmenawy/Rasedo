<?php

namespace App\Services\Customers;

use App\Models\PublicSettings\ActivityLog;
use App\Models\Customers\Customer;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Services\Accounting\AccountingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly AccountingService $accounting,
    ) {}

    public function paginate(?string $search, ?string $status): LengthAwarePaginator
    {
        return $this->customers->paginateFiltered($search, $status);
    }

    public function accounts(): LengthAwarePaginator
    {
        return $this->customers->paginateAccounts();
    }

    public function create(array $data): Customer
    {
        $data['code'] = $data['code'] ?? $this->customers->nextCode();
        $data['created_by'] = auth()->id();
        $data['opening_balance'] = (float) ($data['opening_balance'] ?? 0);
        $data['credit_limit'] = (float) ($data['credit_limit'] ?? 0);
        $data['current_balance'] = $data['opening_balance'];
        $customer = $this->customers->create($data);
        ActivityLog::record('customers', 'create', "إضافة عميل {$customer->name}", $customer);

        return $customer;
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer = $this->customers->update($customer, $data);
        ActivityLog::record('customers', 'update', "تعديل عميل {$customer->name}", $customer);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $this->customers->delete($customer);
    }

    public function profile(Customer $customer): Customer
    {
        return $this->customers->loadProfile($customer);
    }

    public function active(): Collection
    {
        return $this->customers->activeOrdered();
    }

    public function collect(Customer $customer, array $data): void
    {
        $this->accounting->recordReceipt($data + [
            'party_type' => Customer::class,
            'party_id' => $customer->id,
        ]);
    }
}
