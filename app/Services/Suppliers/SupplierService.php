<?php

namespace App\Services\Suppliers;

use App\Models\PublicSettings\ActivityLog;
use App\Models\Suppliers\Supplier;
use App\Repositories\Suppliers\Contracts\SupplierRepositoryInterface;
use App\Services\Accounting\AccountingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepositoryInterface $suppliers,
        private readonly AccountingService $accounting,
    ) {}

    public function paginate(?string $search, ?string $status): LengthAwarePaginator
    {
        return $this->suppliers->paginateFiltered($search, $status);
    }

    public function accounts(): LengthAwarePaginator
    {
        return $this->suppliers->paginateAccounts();
    }

    public function create(array $data): Supplier
    {
        $data['code'] = $data['code'] ?? $this->suppliers->nextCode();
        $data['created_by'] = auth()->id();
        $data['opening_balance'] = (float) ($data['opening_balance'] ?? 0);
        $data['current_balance'] = $data['opening_balance'];
        $supplier = $this->suppliers->create($data);
        ActivityLog::record('suppliers', 'create', "إضافة مورد {$supplier->name}", $supplier);

        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier = $this->suppliers->update($supplier, $data);
        ActivityLog::record('suppliers', 'update', "تعديل مورد {$supplier->name}", $supplier);

        return $supplier;
    }

    public function delete(Supplier $supplier): void
    {
        $this->suppliers->delete($supplier);
    }

    public function profile(Supplier $supplier): Supplier
    {
        return $this->suppliers->loadProfile($supplier);
    }

    public function active(): Collection
    {
        return $this->suppliers->activeOrdered();
    }

    public function pay(Supplier $supplier, array $data): void
    {
        $this->accounting->recordPayment($data + [
            'party_type' => Supplier::class,
            'party_id' => $supplier->id,
        ]);
    }
}
