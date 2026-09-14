<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Aliados (Empresas o propietarios aliados de vehículos)
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nit', 30)->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Clientes (Empresas o personas naturales que contratan el transporte)
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Empresa', 'Persona Natural'])->default('Empresa');
            $table->string('business_name')->nullable(); // Razón Social si es Empresa
            $table->string('document_number', 30)->index(); // NIT o CC
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Empleados / Conductores
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('name');
            $table->string('document_number', 30)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->enum('employee_type', ['Conductor', 'Administrativo', 'Operativo'])->default('Conductor');
            $table->string('contract_number', 50)->nullable();
            $table->enum('contract_type', ['Termino Fijo', 'Termino Indefinido', 'Prestacion Servicios', 'Otro'])->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('driver_license_number', 50)->nullable();
            $table->string('driver_license_category', 10)->nullable();
            $table->date('driver_license_expiration')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Vehículos
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 10)->unique(); // Placa
            $table->string('internal_number', 20)->nullable(); // Número interno
            $table->string('brand', 50); // Marca
            $table->string('line', 50)->nullable(); // Línea
            $table->integer('model_year'); // Modelo (año)
            $table->string('color', 40)->nullable();
            $table->string('engine_number', 60)->nullable();
            $table->string('chassis_number', 60)->nullable();
            $table->enum('vehicle_type', ['Automovil', 'Camioneta', 'Van', 'Microbus', 'Buseta', 'Bus'])->default('Van');
            $table->integer('passenger_capacity')->default(4);
            $table->decimal('current_mileage', 12, 2)->default(0);

            // Documentos reglamentarios colombianos
            $table->string('soat_number', 50)->nullable();
            $table->date('soat_expiration')->nullable();
            $table->string('technomechanical_number', 50)->nullable();
            $table->date('technomechanical_expiration')->nullable();
            $table->string('contractual_policy_number', 50)->nullable();
            $table->date('contractual_policy_expiration')->nullable();
            $table->string('extra_contractual_policy_number', 50)->nullable();
            $table->date('extra_contractual_policy_expiration')->nullable();
            $table->string('operation_card_number', 50)->nullable();
            $table->date('operation_card_expiration')->nullable();

            // Vinculación
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('default_driver_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->enum('status', ['Activo', 'Mantenimiento', 'Inactivo'])->default('Activo');
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Partes / Componentes de Vehículos (desgaste por kilometraje)
        Schema::create('vehicle_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('name'); // Aceite, Llantas, Pastillas de freno, etc.
            $table->decimal('replacement_mileage', 12, 2)->default(0); // Kilometraje al momento del cambio
            $table->decimal('lifespan_km', 12, 2)->default(10000); // Vida útil recomendada
            $table->date('replacement_date')->nullable();
            $table->string('brand')->nullable();
            $table->enum('status', ['Optimo', 'Alerta', 'Vencido'])->default('Optimo');
            $table->timestamps();
        });

        // 6. Contratos y Convenios de Transporte
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->text('contract_object'); // Objeto del contrato
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('value', 14, 2)->nullable();
            $table->enum('contract_type', ['Empresarial', 'Turismo', 'Escolar', 'Salud', 'Grupo Especifico'])->default('Empresarial');
            $table->enum('status', ['Vigente', 'Vencido', 'Cancelado'])->default('Vigente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('vehicle_parts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('partners');
    }
};
