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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('origin', 255);
            $table->string('destination', 255);
            $table->text('description')->nullable();
            $table->enum('route_type', ['Urbana', 'Rural', 'Intermunicipal', 'Escolar', 'Empresarial', 'Turismo'])->default('Urbana');
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->enum('status', ['Activa', 'Inactiva'])->default('Activa')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
