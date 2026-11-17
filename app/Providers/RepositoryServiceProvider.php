<?php

namespace App\Providers;

use App\Repositories\Accounting\Contracts\AccountRepositoryInterface;
use App\Repositories\Accounting\Contracts\JournalRepositoryInterface;
use App\Repositories\Accounting\Eloquent\AccountRepository;
use App\Repositories\Accounting\Eloquent\JournalRepository;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Repositories\Customers\Eloquent\CustomerRepository;
use App\Repositories\Expenses\Contracts\MoneyCategoryRepositoryInterface;
use App\Repositories\Expenses\Contracts\MoneyEntryRepositoryInterface;
use App\Repositories\Expenses\Eloquent\MoneyCategoryRepository;
use App\Repositories\Expenses\Eloquent\MoneyEntryRepository;
use App\Repositories\Inventory\Contracts\InventoryRepositoryInterface;
use App\Repositories\Inventory\Eloquent\InventoryRepository;
use App\Repositories\Products\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Repositories\Products\Eloquent\ProductCategoryRepository;
use App\Repositories\Products\Eloquent\ProductRepository;
use App\Repositories\PublicSettings\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\PermissionRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\RoleRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\UserRepositoryInterface;
use App\Repositories\PublicSettings\Eloquent\ActivityLogRepository;
use App\Repositories\PublicSettings\Eloquent\PermissionRepository;
use App\Repositories\PublicSettings\Eloquent\RoleRepository;
use App\Repositories\PublicSettings\Eloquent\UserRepository;
use App\Repositories\Purchases\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Purchases\Eloquent\PurchaseInvoiceRepository;
use App\Repositories\Reports\Contracts\DashboardRepositoryInterface;
use App\Repositories\Reports\Contracts\ReportRepositoryInterface;
use App\Repositories\Reports\Eloquent\DashboardRepository;
use App\Repositories\Reports\Eloquent\ReportRepository;
use App\Repositories\Sales\Contracts\SalesInvoiceRepositoryInterface;
use App\Repositories\Sales\Eloquent\SalesInvoiceRepository;
use App\Repositories\Suppliers\Contracts\SupplierRepositoryInterface;
use App\Repositories\Suppliers\Eloquent\SupplierRepository;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Repositories\Treasury\Eloquent\TreasuryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $bindings = [
            CustomerRepositoryInterface::class => CustomerRepository::class,
            SupplierRepositoryInterface::class => SupplierRepository::class,
            ProductRepositoryInterface::class => ProductRepository::class,
            ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
            InventoryRepositoryInterface::class => InventoryRepository::class,
            MoneyEntryRepositoryInterface::class => MoneyEntryRepository::class,
            MoneyCategoryRepositoryInterface::class => MoneyCategoryRepository::class,
            TreasuryRepositoryInterface::class => TreasuryRepository::class,
            AccountRepositoryInterface::class => AccountRepository::class,
            JournalRepositoryInterface::class => JournalRepository::class,
            UserRepositoryInterface::class => UserRepository::class,
            RoleRepositoryInterface::class => RoleRepository::class,
            PermissionRepositoryInterface::class => PermissionRepository::class,
            ActivityLogRepositoryInterface::class => ActivityLogRepository::class,
            SalesInvoiceRepositoryInterface::class => SalesInvoiceRepository::class,
            PurchaseInvoiceRepositoryInterface::class => PurchaseInvoiceRepository::class,
            DashboardRepositoryInterface::class => DashboardRepository::class,
            ReportRepositoryInterface::class => ReportRepository::class,
        ];

        foreach ($bindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
