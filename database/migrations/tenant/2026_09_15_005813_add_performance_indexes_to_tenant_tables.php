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
        // 1. Índices en Vehículos
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('status', 'vehicles_status_idx');
            $table->index('soat_expiration', 'vehicles_soat_exp_idx');
            $table->index('technomechanical_expiration', 'vehicles_rtm_exp_idx');
            $table->index('operation_card_expiration', 'vehicles_to_exp_idx');
        });

        // 2. Índices en Empleados / Conductores
        Schema::table('employees', function (Blueprint $table) {
            $table->index('status', 'employees_status_idx');
            $table->index('driver_license_expiration', 'employees_lic_exp_idx');
        });

        // 3. Índices en Órdenes de Servicio
        Schema::table('service_orders', function (Blueprint $table) {
            $table->index('status', 'service_orders_status_idx');
            $table->index('scheduled_start_time', 'service_orders_start_time_idx');
        });

        // 4. Índices en Documentos FUEC
        Schema::table('fuec_documents', function (Blueprint $table) {
            $table->index('status', 'fuec_status_idx');
            $table->index('issue_date', 'fuec_issue_date_idx');
        });

        // 5. Índices en Combustible y Mantenimientos
        if (Schema::hasTable('fuel_refills')) {
            Schema::table('fuel_refills', function (Blueprint $table) {
                $table->index('refill_date', 'fuel_refills_date_idx');
            });
        }

        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->index('status', 'maintenances_status_idx');
                $table->index('service_date', 'maintenances_date_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_status_idx');
            $table->dropIndex('vehicles_soat_exp_idx');
            $table->dropIndex('vehicles_rtm_exp_idx');
            $table->dropIndex('vehicles_to_exp_idx');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_status_idx');
            $table->dropIndex('employees_lic_exp_idx');
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropIndex('service_orders_status_idx');
            $table->dropIndex('service_orders_start_time_idx');
        });

        Schema::table('fuec_documents', function (Blueprint $table) {
            $table->dropIndex('fuec_status_idx');
            $table->dropIndex('fuec_issue_date_idx');
        });

        if (Schema::hasTable('fuel_refills')) {
            Schema::table('fuel_refills', function (Blueprint $table) {
                $table->dropIndex('fuel_refills_date_idx');
            });
        }

        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->dropIndex('maintenances_status_idx');
                $table->dropIndex('maintenances_date_idx');
            });
        }
    }
};
