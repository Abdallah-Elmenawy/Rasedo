<?php

namespace App\Repositories\Suppliers\Eloquent;

use App\Enums\InvoiceStatus;
use App\Enums\PartyStatus;
use App\Models\Suppliers\Supplier;
use App\Repositories\Suppliers\Contracts\SupplierRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function paginateFiltered(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        $query = Supplier::with('creator');
        GridQuery::apply($query, [
            'code' => ['column' => 'code', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'phone' => ['column' => 'phone', 'type' => 'text'],
            'address' => ['column' => 'address', 'type' => 'text'],
            'tax_number' => ['column' => 'tax_number', 'type' => 'text'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function paginateAccounts(int $perPage = 15): LengthAwarePaginator
    {
        $posted = InvoiceStatus::posted();

        $query = Supplier::query()
            ->withSum(['invoices as purchases_total' => fn ($q) => $q->whereIn('status', $posted)], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', $posted)], 'paid');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
        ]);
        if (request('purchases') !== null && request('purchases') !== '') {
            $query->having('purchases_total', '=', request('purchases'));
        }
        if (request('paid') !== null && request('paid') !== '') {
            $query->having('paid_total', '=', request('paid'));
        }

        return $query->orderByDesc('current_balance')->paginate($perPage)->withQueryString();
    }

    public function nextCode(): string
    {
        return 'SUP-'.str_pad((string) (Supplier::query()->max('id') + 1), 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Supplier
    {
        return Supplier::query()->create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier;
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    public function loadProfile(Supplier $supplier): Supplier
    {
        return $supplier->load(['invoices' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest()]);
    }

    public function orderedByName(): Collection
    {
        return Supplier::query()->orderBy('name')->get();
    }

    public function activeOrdered(): Collection
    {
        return Supplier::query()->where('status', PartyStatus::Active->value)->orderBy('name')->get();
    }
}
