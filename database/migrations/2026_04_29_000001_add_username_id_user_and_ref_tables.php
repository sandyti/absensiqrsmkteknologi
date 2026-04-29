<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'id_user')) {
                $table->string('id_user', 36)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'id_ref')) {
                $table->unsignedBigInteger('id_ref')->nullable()->after('role');
                $table->index('id_ref');
            }
        });

        if (! Schema::hasTable('gurus')) {
            Schema::create('gurus', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('identifier')->nullable();
                $table->string('teaches_class')->nullable();
                $table->string('subject')->nullable();
                $table->string('teaching_hours')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('siswas')) {
            Schema::create('siswas', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('identifier')->nullable();
                $table->string('classroom')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('users', 'email')) {
            DB::table('users')->whereNull('username')->update([
                'username' => DB::raw('email'),
            ]);
        }

        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                if (blank($user->id_user)) {
                    DB::table('users')->where('id', $user->id)->update([
                        'id_user' => (string) \Illuminate\Support\Str::ulid(),
                    ]);
                }

                if ($user->role === 'guru' && ! DB::table('gurus')->where('id', $user->id_ref)->exists()) {
                    $guruId = DB::table('gurus')->insertGetId([
                        'name' => $user->name,
                        'identifier' => $user->identifier,
                        'teaches_class' => $user->teaches_class,
                        'subject' => $user->subject,
                        'teaching_hours' => $user->teaching_hours,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('users')->where('id', $user->id)->update(['id_ref' => $guruId]);
                }

                if ($user->role === 'siswa' && ! DB::table('siswas')->where('id', $user->id_ref)->exists()) {
                    $siswaId = DB::table('siswas')->insertGetId([
                        'name' => $user->name,
                        'identifier' => $user->identifier,
                        'classroom' => $user->classroom,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('users')->where('id', $user->id)->update(['id_ref' => $siswaId]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'id_ref')) {
                $table->dropIndex(['id_ref']);
                $table->dropColumn('id_ref');
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('users', 'id_user')) {
                $table->dropUnique(['id_user']);
                $table->dropColumn('id_user');
            }
        });

        Schema::dropIfExists('siswas');
        Schema::dropIfExists('gurus');
    }
};
