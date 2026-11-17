<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_settings', 'brand_logo_side')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->string('brand_logo_side', 10)->default('left')->after('show_brand_logo');
            });
        }

        Cache::forget('company_settings');
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_settings', 'brand_logo_side')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('brand_logo_side');
            });
        }

        Cache::forget('company_settings');
    }
};
