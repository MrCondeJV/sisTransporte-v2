<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Órdenes de Servicio
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('support_driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();

            // Ruta y Horarios
            $table->string('origin');
            $table->string('destination');
            $table->string('route_name')->nullable();
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();

            // Detalles del servicio
            $table->string('passenger_contact_name')->nullable();
            $table->string('passenger_contact_phone')->nullable();
            $table->integer('passengers_count')->default(1);
            $table->text('service_notes')->nullable();

            // Kilometraje
            $table->decimal('start_mileage', 12, 2)->nullable();
            $table->decimal('end_mileage', 12, 2)->nullable();

            // Facturación vinculada
            $table->string('invoice_number', 50)->nullable();
            $table->string('invoice_file')->nullable();

            // Ciclo de vida
            $table->enum('status', ['Pendiente', 'Asignada', 'En Progreso', 'Finalizada', 'Cancelada'])->default('Pendiente');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Checklist Preoperacional (Diario obligatorio por norma de transporte)
        Schema::create('preoperational_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->date('date')->index();
            $table->time('time');

            // Inspecciones mecánicas y de seguridad (JSON estructurado)
            $table->json('fluid_levels'); // Aceite, frenos, refrigerante, dirección
            $table->json('lights_and_electrical'); // Luces altas, bajas, direccionales, pito, alarma reversa
            $table->json('tires_and_brakes'); // Presión, desgaste, labrado, freno de emergencia
            $table->json('safety_kit'); // Botiquín, extintor vigente, conos, llanta repuesto, gato
            $table->json('cabin_and_belts'); // Cinturones, espejos, vidrios, puertas

            $table->decimal('mileage', 12, 2);
            $table->boolean('is_approved')->default(true);
            $table->text('observations')->nullable();
            $table->longText('driver_signature')->nullable(); // Firma digital / base64

            $table->timestamps();
        });

        // 3. Documentos FUEC (Formato Único de Extracto de Contrato)
        Schema::create('fuec_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->string('fuec_number', 50)->unique(); // Número oficial de 12 dígitos
            $table->string('resolution_number', 50)->nullable(); // Resolución MinTransporte
            $table->date('issue_date');
            $table->date('expiration_date');
            $table->text('qr_code_content')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['Emitido', 'Anulado', 'Vencido'])->default('Emitido');
            $table->timestamps();
        });

        // 4. Novedades / Incidentes en Operación
        Schema::create('service_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('driver_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('incident_type', ['Mecanica', 'Trafico', 'Accidente', 'Pasajero', 'Clima', 'Otro']);
            $table->text('description');
            $table->json('photos')->nullable();
            $table->dateTime('reported_at');
            $table->enum('status', ['Abierta', 'En Revision', 'Resuelta'])->default('Abierta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_incidents');
        Schema::dropIfExists('fuec_documents');
        Schema::dropIfExists('preoperational_checklists');
        Schema::dropIfExists('service_orders');
    }
};
