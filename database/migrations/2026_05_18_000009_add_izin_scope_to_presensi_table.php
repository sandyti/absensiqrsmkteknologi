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
        Schema::table('presensi', function (Blueprint $table): void {
            if (! Schema::hasColumn('presensi', 'izin_scope')) {
                $table->enum('izin_scope', ['session', 'full_day'])->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table): void {
            if (Schema::hasColumn('presensi', 'izin_scope')) {
                $table->dropColumn('izin_scope');
            }
        });
    }
};
