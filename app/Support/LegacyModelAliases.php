<?php

namespace App\Support;

use App\Models\Accounting\Account;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\AllUsers\User;
use App\Models\Customers\Customer;
use App\Models\Expenses\MoneyCategory;
use App\Models\Expenses\MoneyEntry;
use App\Models\Inventory\InventoryMovement;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\PublicSettings\ActivityLog;
use App\Models\PublicSettings\CompanySetting;
use App\Models\PublicSettings\Permission;
use App\Models\PublicSettings\Role;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseInvoiceItem;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Purchases\PurchaseReturnItem;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Models\Sales\SalesReturn;
use App\Models\Sales\SalesReturnItem;
use App\Models\Suppliers\Supplier;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Treasury\Treasury;
use App\Models\Treasury\TreasuryTransfer;

class LegacyModelAliases
{
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $target = self::map()[$class] ?? null;

            if ($target) {
                class_alias($target, $class);
            }
        }, true, true);
    }

    /** @return array<string, class-string> */
    public static function map(): array
    {
        return [
            'App\\Models\\Customer' => Customer::class,
            'App\\Models\\Supplier' => Supplier::class,
            'App\\Models\\Product' => Product::class,
            'App\\Models\\ProductCategory' => ProductCategory::class,
            'App\\Models\\InventoryMovement' => InventoryMovement::class,
            'App\\Models\\SalesInvoice' => SalesInvoice::class,
            'App\\Models\\SalesInvoiceItem' => SalesInvoiceItem::class,
            'App\\Models\\SalesReturn' => SalesReturn::class,
            'App\\Models\\SalesReturnItem' => SalesReturnItem::class,
            'App\\Models\\PurchaseInvoice' => PurchaseInvoice::class,
            'App\\Models\\PurchaseInvoiceItem' => PurchaseInvoiceItem::class,
            'App\\Models\\PurchaseReturn' => PurchaseReturn::class,
            'App\\Models\\PurchaseReturnItem' => PurchaseReturnItem::class,
            'App\\Models\\Account' => Account::class,
            'App\\Models\\Journal' => Journal::class,
            'App\\Models\\JournalLine' => JournalLine::class,
            'App\\Models\\Treasury' => Treasury::class,
            'App\\Models\\TreasuryTransfer' => TreasuryTransfer::class,
            'App\\Models\\MoneyTransaction' => MoneyTransaction::class,
            'App\\Models\\MoneyEntry' => MoneyEntry::class,
            'App\\Models\\MoneyCategory' => MoneyCategory::class,
            'App\\Models\\Role' => Role::class,
            'App\\Models\\Permission' => Permission::class,
            'App\\Models\\ActivityLog' => ActivityLog::class,
            'App\\Models\\CompanySetting' => CompanySetting::class,
            'App\\Models\\User' => User::class,
        ];
    }
}
