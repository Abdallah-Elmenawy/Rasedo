<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = [
    ['money_transactions', 'party_type'],
    ['money_transactions', 'invoice_type'],
    ['journals', 'reference_type'],
    ['inventory_movements', 'reference_type'],
    ['activity_logs', 'auditable_type'],
];

$tables = [
    'money_transactions' => ['party_type', 'invoice_type'],
    'journals' => ['reference_type'],
    'inventory_movements' => ['reference_type'],
    'activity_logs' => ['auditable_type'],
];

foreach ($tables as $table => $columns) {
    foreach ($columns as $column) {
        $values = DB::table($table)
            ->where($column, 'like', 'App\\\\Models\\\\%')
            ->select($column)
            ->distinct()
            ->pluck($column);
        foreach ($values as $value) {
            if (substr_count($value, '\\') === 2) {
                echo "OLD {$table}.{$column} = {$value}\n";
            }
        }
    }
}

echo "scan-done\n";
