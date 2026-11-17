<?php

namespace Database\Seeders\all_users;

use App\Models\Accounting\Account;
use App\Models\Treasury\Treasury;
use Illuminate\Database\Seeder;

class TreasuryLayerSeeder extends Seeder
{
    public function run(): void
    {
        $cashAccount = Account::query()->where('code', '1100')->value('id');
        $bankAccount = Account::query()->where('code', '1200')->value('id');

        Treasury::query()->updateOrCreate(['name' => 'الخزينة الرئيسية'], [
            'type' => 'cash',
            'account_id' => $cashAccount,
            'opening_balance' => 10000,
            'current_balance' => 10000,
            'is_active' => true,
        ]);
        Treasury::query()->updateOrCreate(['name' => 'الحساب البنكي'], [
            'type' => 'bank',
            'account_id' => $bankAccount,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);
    }
}
