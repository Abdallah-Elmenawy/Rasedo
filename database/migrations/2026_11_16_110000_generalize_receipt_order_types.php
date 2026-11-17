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
        if (Schema::hasColumn('company_settings', 'receipt_order_dine_in')) {
            DB::table('company_settings')->where('receipt_order_dine_in', 'صالة')->update([
                'receipt_order_dine_in' => 'من المحل',
            ]);
            Schema::table('company_settings', function (Blueprint $table) {
                $table->renameColumn('receipt_order_dine_in', 'receipt_order_in_store');
            });
        }

        if (Schema::hasColumn('company_settings', 'receipt_order_takeaway')) {
            DB::table('company_settings')->where('receipt_order_takeaway', 'تيك أواي')->update([
                'receipt_order_takeaway' => 'استلام',
            ]);
            Schema::table('company_settings', function (Blueprint $table) {
                $table->renameColumn('receipt_order_takeaway', 'receipt_order_pickup');
            });
        }

        if (Schema::hasColumn('company_settings', 'receipt_order_delivery')) {
            DB::table('company_settings')->where('receipt_order_delivery', 'دليفري')->update([
                'receipt_order_delivery' => 'توصيل',
            ]);
        }

        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('receipt_order_in_store', 40)->default('من المحل')->change();
            $table->string('receipt_order_pickup', 40)->default('استلام')->change();
            $table->string('receipt_order_delivery', 40)->default('توصيل')->change();
        });

        DB::table('sales_invoices')->where('order_type', 'dine_in')->update(['order_type' => 'in_store']);
        DB::table('sales_invoices')->where('order_type', 'takeaway')->update(['order_type' => 'pickup']);

        DB::table('sales_invoices')->where('notes', 'نقطة بيع — صالة')->update(['notes' => 'نقطة بيع — من المحل']);
        DB::table('sales_invoices')->where('notes', 'نقطة بيع — تيك أواي')->update(['notes' => 'نقطة بيع — استلام']);
        DB::table('sales_invoices')->where('notes', 'نقطة بيع — دليفري')->update(['notes' => 'نقطة بيع — توصيل']);

        Cache::forget('company_settings');
    }

    public function down(): void
    {
        DB::table('sales_invoices')->where('order_type', 'in_store')->update(['order_type' => 'dine_in']);
        DB::table('sales_invoices')->where('order_type', 'pickup')->update(['order_type' => 'takeaway']);

        DB::table('sales_invoices')->where('notes', 'نقطة بيع — من المحل')->update(['notes' => 'نقطة بيع — صالة']);
        DB::table('sales_invoices')->where('notes', 'نقطة بيع — استلام')->update(['notes' => 'نقطة بيع — تيك أواي']);
        DB::table('sales_invoices')->where('notes', 'نقطة بيع — توصيل')->update(['notes' => 'نقطة بيع — دليفري']);

        if (Schema::hasColumn('company_settings', 'receipt_order_in_store')) {
            DB::table('company_settings')->where('receipt_order_in_store', 'من المحل')->update([
                'receipt_order_in_store' => 'صالة',
            ]);
            Schema::table('company_settings', function (Blueprint $table) {
                $table->renameColumn('receipt_order_in_store', 'receipt_order_dine_in');
            });
        }

        if (Schema::hasColumn('company_settings', 'receipt_order_pickup')) {
            DB::table('company_settings')->where('receipt_order_pickup', 'استلام')->update([
                'receipt_order_pickup' => 'تيك أواي',
            ]);
            Schema::table('company_settings', function (Blueprint $table) {
                $table->renameColumn('receipt_order_pickup', 'receipt_order_takeaway');
            });
        }

        DB::table('company_settings')->where('receipt_order_delivery', 'توصيل')->update([
            'receipt_order_delivery' => 'دليفري',
        ]);

        Cache::forget('company_settings');
    }
};
