<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 6, 2)->default(0); // km/h
            $table->decimal('heading', 6, 2)->nullable(); // Rumbo / orientación en grados (0 - 360)
            $table->decimal('accuracy', 6, 2)->nullable(); // Precisión en metros
            $table->dateTime('device_timestamp')->index(); // Fecha/hora del GPS del móvil
            $table->timestamp('created_at')->useCurrent()->index();

            // Índices optimizados para consultas en vivo y dibujo de rutas
            $table->index(['vehicle_id', 'device_timestamp']);
            $table->index(['service_order_id', 'device_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_locations');
    }
};
