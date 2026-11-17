<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('receipt_sales_title', 40)->default('المبيعات')->after('print_format');
            $table->string('receipt_order_dine_in', 40)->default('صالة')->after('receipt_sales_title');
            $table->string('receipt_order_takeaway', 40)->default('تيك أواي')->after('receipt_order_dine_in');
            $table->string('receipt_order_delivery', 40)->default('دليفري')->after('receipt_order_takeaway');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('order_type', 20)->nullable()->after('notes');
        });

        $map = [
            'نقطة بيع — صالة' => 'dine_in',
            'نقطة بيع — تيك أواي' => 'takeaway',
            'نقطة بيع — دليفري' => 'delivery',
        ];
        foreach ($map as $notes => $type) {
            DB::table('sales_invoices')->where('notes', $notes)->update(['order_type' => $type]);
        }
        Cache::forget('company_settings');
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_sales_title',
                'receipt_order_dine_in',
                'receipt_order_takeaway',
                'receipt_order_delivery',
            ]);
        });
    }
};
