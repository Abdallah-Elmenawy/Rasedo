<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_settings', 'avatar')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('logo');
            });
        }

        Cache::forget('company_settings');
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_settings', 'avatar')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }

        Cache::forget('company_settings');
    }
};
