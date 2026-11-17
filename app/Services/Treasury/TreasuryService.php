<?php

namespace App\Services\Treasury;

use App\Models\Customers\Customer;
use App\Models\Suppliers\Supplier;
use App\Models\Treasury\Treasury;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Repositories\Suppliers\Contracts\SupplierRepositoryInterface;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Services\Accounting\AccountingService;

class TreasuryService
{
    public function __construct(
        private readonly TreasuryRepositoryInterface $treasuries,
        private readonly CustomerRepositoryInterface $customers,
        private readonly SupplierRepositoryInterface $suppliers,
        private readonly AccountingService $accounting,
    ) {}

    public function page(): array
    {
        return [
            'treasuriesAll' => $this->treasuries->allWithAccount(),
            'treasuries' => $this->treasuries->active(),
            'customers' => $this->customers->activeOrdered(),
            'suppliers' => $this->suppliers->activeOrdered(),
            'saleInvoices' => $this->treasuries->openSalesInvoices(),
            'purchaseInvoices' => $this->treasuries->openPurchaseInvoices(),
            'receipts' => $this->treasuries->paginateReceipts(),
            'payments' => $this->treasuries->paginatePayments(),
            'transfers' => $this->treasuries->paginateTransfers(),
        ];
    }

    public function active()
    {
        return $this->treasuries->active();
    }

    public function create(array $data): Treasury
    {
        return $this->treasuries->create($data + [
            'current_balance' => $data['opening_balance'] ?? 0,
            'is_active' => true,
        ]);
    }

    public function receipt(array $data): void
    {
        $this->accounting->recordReceipt($data + [
            'party_type' => Customer::class,
            'party_id' => $data['customer_id'],
        ]);
    }

    public function payment(array $data): void
    {
        $this->accounting->recordPayment($data + [
            'party_type' => Supplier::class,
            'party_id' => $data['supplier_id'],
        ]);
    }

    public function transfer(array $data): void
    {
        $this->accounting->transfer($data);
    }
}
