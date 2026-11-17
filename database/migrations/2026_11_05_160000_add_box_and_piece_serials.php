<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('pieces_per_box')->nullable()->after('track_serial');
        });

        Schema::table('product_serials', function (Blueprint $table) {
            $table->string('kind', 10)->default('piece')->after('serial');
            $table->foreignId('purchase_invoice_id')->nullable()->after('sales_invoice_id')->constrained('purchase_invoices')->nullOnDelete();
        });

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->json('serials')->nullable()->after('description');
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->json('serials')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropColumn('serials');
        });
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropColumn('serials');
        });
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_invoice_id');
            $table->dropColumn('kind');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('pieces_per_box');
        });
    }
};
