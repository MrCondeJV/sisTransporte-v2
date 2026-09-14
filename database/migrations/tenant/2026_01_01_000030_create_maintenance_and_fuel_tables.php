<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mantenimientos
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('maintenance_type', ['Preventivo', 'Correctivo'])->default('Preventivo');
            $table->date('maintenance_date');
            $table->decimal('mileage', 12, 2);
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('workshop_name')->nullable(); // Taller o proveedor mecánico
            $table->text('details');
            $table->string('receipt_file')->nullable(); // Soporte en PDF o imagen
            $table->json('replaced_parts')->nullable(); // IDs o nombres de partes cambiadas
            $table->timestamps();
        });

        // 2. Recargas de Combustible
        Schema::create('fuel_refills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->date('refill_date')->index();
            $table->decimal('gallons', 10, 3); // Cantidad en galones
            $table->decimal('total_cost', 12, 2); // Costo total pagado
            $table->decimal('price_per_gallon', 10, 2)->nullable(); // Precio por galón
            $table->decimal('odometer_mileage', 12, 2); // Kilometraje en el tablero al recargar
            $table->decimal('distance_since_last_refill', 10, 2)->nullable(); // Km recorridos entre recargas
            $table->decimal('calculated_performance', 10, 2)->nullable(); // Km por galón
            $table->string('gas_station_name')->nullable(); // Estación de servicio
            $table->string('odometer_photo')->nullable(); // Foto del odómetro
            $table->string('receipt_photo')->nullable(); // Foto del recibo / factura
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_refills');
        Schema::dropIfExists('maintenances');
    }
};
