<?php

namespace Database\Seeders\all_users;

use App\Models\Accounting\Account;
use Illuminate\Database\Seeder;

class AccountingLayerSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'الأصول', 'type' => 'asset', 'parent' => null],
            ['code' => '1100', 'name' => 'الصندوق', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1200', 'name' => 'البنك', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1300', 'name' => 'العملاء', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1400', 'name' => 'المخزون', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '2000', 'name' => 'الالتزامات', 'type' => 'liability', 'parent' => null],
            ['code' => '2100', 'name' => 'الموردون', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '2200', 'name' => 'ضريبة المبيعات', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => 'equity', 'parent' => null],
            ['code' => '3100', 'name' => 'رأس المال', 'type' => 'equity', 'parent' => '3000'],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => 'revenue', 'parent' => null],
            ['code' => '4100', 'name' => 'إيرادات المبيعات', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '4200', 'name' => 'إيرادات أخرى', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '5000', 'name' => 'المصروفات', 'type' => 'expense', 'parent' => null],
            ['code' => '5100', 'name' => 'تكلفة المبيعات', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '5200', 'name' => 'مصروفات تشغيلية', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '5300', 'name' => 'خصم مسموح به', 'type' => 'expense', 'parent' => '5000'],
        ];

        $created = [];
        foreach ($accounts as $row) {
            $account = Account::query()->updateOrCreate(['code' => $row['code']], [
                'name' => $row['name'],
                'type' => $row['type'],
                'parent_id' => $row['parent'] ? ($created[$row['parent']] ?? null) : null,
                'is_system' => true,
                'is_active' => true,
            ]);
            $created[$row['code']] = $account->id;
        }
    }
}
