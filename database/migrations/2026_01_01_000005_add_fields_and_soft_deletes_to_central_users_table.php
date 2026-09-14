<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'username')) {
                $columns[] = 'username';
            }
            if (Schema::hasColumn('users', 'phone')) {
                $columns[] = 'phone';
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $columns[] = 'avatar';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $columns[] = 'is_active';
            }
            if (Schema::hasColumn('users', 'deleted_at')) {
                $columns[] = 'deleted_at';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
