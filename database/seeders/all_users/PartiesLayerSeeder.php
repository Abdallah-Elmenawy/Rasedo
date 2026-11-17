<?php

namespace Database\Seeders\all_users;

use App\Models\AllUsers\User;
use App\Models\Customers\Customer;
use App\Models\Suppliers\Supplier;
use Illuminate\Database\Seeder;

class PartiesLayerSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@admin.com')->value('id');

        Customer::query()->updateOrCreate(['code' => 'CUS-0001'], [
            'name' => 'عميل تجريبي',
            'phone' => '01111111111',
            'status' => 'active',
            'created_by' => $adminId,
        ]);
        Supplier::query()->updateOrCreate(['code' => 'SUP-0001'], [
            'name' => 'مورد تجريبي',
            'phone' => '01222222222',
            'status' => 'active',
            'created_by' => $adminId,
        ]);
    }
}
