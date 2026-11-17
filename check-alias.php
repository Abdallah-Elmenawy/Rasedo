<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo class_exists('App\\Models\\Customer') ? "alias-ok\n" : "alias-missing\n";
echo App\Models\Customer::class, "\n";

$rows = Illuminate\Support\Facades\DB::table('money_transactions')
    ->select('party_type')
    ->distinct()
    ->pluck('party_type');

echo $rows->implode(' | '), "\n";

$tx = App\Models\Treasury\MoneyTransaction::with('party')->where('type', 'receipt')->latest()->limit(1)->first();
echo $tx ? ('receipt-party '.$tx->party_type.' '.get_class($tx->party))."\n" : "no-receipt\n";
