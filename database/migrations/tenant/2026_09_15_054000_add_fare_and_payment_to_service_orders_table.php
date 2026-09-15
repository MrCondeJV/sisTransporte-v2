<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('service_orders', 'fare')) {
                $table->decimal('fare', 14, 2)->default(0)->after('service_notes');
            }
            if (! Schema::hasColumn('service_orders', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('invoice_file');
            }
            if (! Schema::hasColumn('service_orders', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('is_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['fare', 'is_paid', 'paid_at']);
        });
    }
};
