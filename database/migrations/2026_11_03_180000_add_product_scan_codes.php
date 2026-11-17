<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('scan_code', 32)->nullable()->unique()->after('sku');
            $table->boolean('track_serial')->default(false)->after('is_active');
        });

        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('serial', 80)->unique();
            $table->string('batch_no', 80)->nullable();
            $table->string('status', 20)->default('in_stock');
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('products')->orderBy('id')->lazy()->each(function ($product) {
            DB::table('products')->where('id', $product->id)->update([
                'scan_code' => 'PRD-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['scan_code']);
            $table->dropColumn(['scan_code', 'track_serial']);
        });
    }
};
