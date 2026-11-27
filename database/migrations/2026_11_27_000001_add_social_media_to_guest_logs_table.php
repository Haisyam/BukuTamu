<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guest_logs', function (Blueprint $table) {
            $table->string('social_media_platform', 20)->nullable()->after('phone');
            $table->string('social_media_handle', 100)->nullable()->after('social_media_platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_logs', function (Blueprint $table) {
            $table->dropColumn(['social_media_platform', 'social_media_handle']);
        });
    }
};
