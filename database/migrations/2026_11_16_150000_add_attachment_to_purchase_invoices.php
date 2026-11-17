<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_invoices', 'attachment')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_invoices', 'attachment')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }
};
