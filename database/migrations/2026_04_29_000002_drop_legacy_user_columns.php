<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }

            if (Schema::hasColumn('users', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }

            foreach (['name', 'identifier', 'classroom', 'teaches_class', 'subject', 'teaching_hours'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'identifier')) {
                $table->string('identifier')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'classroom')) {
                $table->string('classroom')->nullable()->after('identifier');
            }

            if (! Schema::hasColumn('users', 'teaches_class')) {
                $table->string('teaches_class')->nullable()->after('classroom');
            }

            if (! Schema::hasColumn('users', 'subject')) {
                $table->string('subject')->nullable()->after('teaches_class');
            }

            if (! Schema::hasColumn('users', 'teaching_hours')) {
                $table->string('teaching_hours')->nullable()->after('subject');
            }
        });
    }
};
