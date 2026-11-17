<?php

namespace App\Repositories\Customers\Eloquent;

use App\Enums\InvoiceStatus;
use App\Enums\PartyStatus;
use App\Models\Customers\Customer;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Support\GridQuery;
use App\Traits\BaseRepository\CrudTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    use CrudTrait;

    protected function modelClass(): string
    {
        return Customer::class;
    }

    public function paginateFiltered(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        $query = Customer::with('creator');
        GridQuery::apply($query, [
            'code' => ['column' => 'code', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'phone' => ['column' => 'phone', 'type' => 'text'],
            'address' => ['column' => 'address', 'type' => 'text'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function paginateAccounts(int $perPage = 15): LengthAwarePaginator
    {
        $posted = InvoiceStatus::posted();

        $query = Customer::query()
            ->withSum(['invoices as sales_total' => fn ($q) => $q->whereIn('status', $posted)], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', $posted)], 'paid');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
        ]);
        if (request('sales') !== null && request('sales') !== '') {
            $query->having('sales_total', '=', request('sales'));
        }
        if (request('paid') !== null && request('paid') !== '') {
            $query->having('paid_total', '=', request('paid'));
        }

        return $query->orderByDesc('current_balance')->paginate($perPage)->withQueryString();
    }

    public function nextCode(): string
    {
        return 'CUS-'.str_pad((string) (Customer::query()->max('id') + 1), 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function loadProfile(Customer $customer): Customer
    {
        return $customer->load(['invoices' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest()]);
    }

    public function orderedByName(): Collection
    {
        return Customer::query()->orderBy('name')->get();
    }

    public function activeOrdered(): Collection
    {
        return Customer::query()->where('status', PartyStatus::Active->value)->orderBy('name')->get();
    }
}
