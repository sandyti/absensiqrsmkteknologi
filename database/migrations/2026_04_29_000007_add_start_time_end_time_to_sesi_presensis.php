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
        Schema::table('sesi_presensis', function (Blueprint $table): void {
            if (! Schema::hasColumn('sesi_presensis', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('tanggal');
            }

            if (! Schema::hasColumn('sesi_presensis', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_presensis', function (Blueprint $table): void {
            if (Schema::hasColumn('sesi_presensis', 'end_time')) {
                $table->dropColumn('end_time');
            }

            if (Schema::hasColumn('sesi_presensis', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
};
