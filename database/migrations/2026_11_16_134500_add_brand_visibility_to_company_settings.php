<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_settings', 'show_brand_name')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->boolean('show_brand_name')->default(true)->after('logo');
            });
        }
        if (! Schema::hasColumn('company_settings', 'show_brand_logo')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->boolean('show_brand_logo')->default(false)->after('show_brand_name');
            });
        }

        Cache::forget('company_settings');
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'show_brand_logo')) {
                $table->dropColumn('show_brand_logo');
            }
            if (Schema::hasColumn('company_settings', 'show_brand_name')) {
                $table->dropColumn('show_brand_name');
            }
        });

        Cache::forget('company_settings');
    }
};
