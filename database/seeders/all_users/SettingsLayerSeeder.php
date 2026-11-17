<?php

namespace Database\Seeders\all_users;

use App\Models\Accounting\Account;
use App\Models\Expenses\MoneyCategory;
use App\Models\PublicSettings\CompanySetting;
use Illuminate\Database\Seeder;

class SettingsLayerSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::query()->whereIn('code', [
            '4100', '1400', '5100', '1300', '2100', '2200', '5300', '5200', '4200',
        ])->pluck('id', 'code');

        CompanySetting::query()->updateOrCreate(['id' => 1], [
            'name' => 'Rasedo',
            'legal_name' => 'شركة راصدو للمحاسبة',
            'email' => 'info@rasedo.test',
            'phone' => '01000000000',
            'address' => 'القاهرة، مصر',
            'currency' => 'EGP',
            'currency_symbol' => 'ج.م',
            'language' => 'ar',
            'sale_prefix' => 'SAL',
            'purchase_prefix' => 'PUR',
            'payment_prefix' => 'PAY',
            'receipt_prefix' => 'REC',
            'journal_prefix' => 'JRN',
            'sales_account_id' => $accounts['4100'] ?? null,
            'purchases_account_id' => $accounts['1400'] ?? null,
            'inventory_account_id' => $accounts['1400'] ?? null,
            'cogs_account_id' => $accounts['5100'] ?? null,
            'customers_account_id' => $accounts['1300'] ?? null,
            'suppliers_account_id' => $accounts['2100'] ?? null,
            'tax_account_id' => $accounts['2200'] ?? null,
            'sales_discount_account_id' => $accounts['5300'] ?? null,
        ]);

        MoneyCategory::query()->updateOrCreate(
            ['name' => 'إيجار', 'type' => 'expense'],
            ['account_id' => $accounts['5200'] ?? null],
        );
        MoneyCategory::query()->updateOrCreate(
            ['name' => 'إيرادات متنوعة', 'type' => 'revenue'],
            ['account_id' => $accounts['4200'] ?? null],
        );
    }
}
