<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Tenant\Client;
use App\Models\Tenant\Employee;
use App\Models\Tenant\PreoperationalChecklist;
use App\Models\Tenant\ServiceIncident;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\OperationValidationService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperationsAndChecklistTest extends TestCase
{
    public function test_operations_lifecycle_checklist_and_validation(): void
    {
        // 1. Inicializar Tenant de prueba
        Tenant::find('test_fase3')?->delete();
        $tenant = Tenant::create([
            'id' => 'test_fase3',
            'name' => 'Transportes Fase 3 S.A.S.',
            'nit' => '901234567-8',
        ]);
        $tenant->domains()->create(['domain' => 'fase3.localhost']);

        tenancy()->initialize($tenant);

        // 2. Crear Cliente
        $client = Client::create([
            'type' => 'Empresa',
            'business_name' => 'Bancolombia S.A.',
            'document_number' => '890903938-8',
            'email' => 'operaciones@bancolombia.com.co',
            'phone' => '6013430000',
            'status' => 'Activo',
        ]);

        // 3. Crear Vehículo Válido y Vehículo con SOAT Vencido
        $validVehicle = Vehicle::create([
            'plate' => 'OKV123',
            'brand' => 'Mercedes-Benz',
            'line' => 'Sprinter',
            'model_year' => 2024,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 16,
            'current_mileage' => 12000,
            'soat_expiration' => now()->addMonths(6),
            'technomechanical_expiration' => now()->addMonths(9),
            'contractual_policy_expiration' => now()->addMonths(7),
            'extra_contractual_policy_expiration' => now()->addMonths(7),
            'operation_card_expiration' => now()->addMonths(11),
            'status' => 'Activo',
        ]);

        $expiredVehicle = Vehicle::create([
            'plate' => 'BAD999',
            'brand' => 'Toyota',
            'line' => 'Hiace',
            'model_year' => 2020,
            'vehicle_type' => 'Van',
            'passenger_capacity' => 14,
            'current_mileage' => 85000,
            'soat_expiration' => now()->subDay(), // Vencido ayer
            'technomechanical_expiration' => now()->addMonths(4),
            'contractual_policy_expiration' => now()->addMonths(3),
            'extra_contractual_policy_expiration' => now()->addMonths(3),
            'operation_card_expiration' => now()->addMonths(5),
            'status' => 'Activo',
        ]);

        // 4. Crear Conductor con Licencia Vigente y Conductor con Licencia Vencida
        $validDriver = Employee::create([
            'name' => 'Jorge Conductor Responsable',
            'document_number' => '79888999',
            'phone' => '3104445566',
            'email' => 'jorge@transporte.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LIC-79888',
            'driver_license_category' => 'C2',
            'driver_license_expiration' => now()->addMonths(8),
            'status' => 'Activo',
        ]);

        $expiredDriver = Employee::create([
            'name' => 'Pedro Licencia Vencida',
            'document_number' => '19222333',
            'phone' => '3118889900',
            'email' => 'pedro@transporte.com',
            'employee_type' => 'Conductor',
            'driver_license_number' => 'LIC-19222',
            'driver_license_category' => 'C1',
            'driver_license_expiration' => now()->subDays(5), // Licencia vencida hace 5 días
            'status' => 'Activo',
        ]);

        // 5. Test de Validación de Cumplimiento Normativo (OperationValidationService)
        $validator = app(OperationValidationService::class);

        // 5.1 Vehículo vencido debe arrojar ValidationException
        $vehicleBlocked = false;
        try {
            $validator->validateAssignment($expiredVehicle, $validDriver);
        } catch (ValidationException $e) {
            $vehicleBlocked = true;
            $this->assertArrayHasKey('vehicle_id', $e->errors());
        }
        $this->assertTrue($vehicleBlocked, 'El vehículo con SOAT vencido debe ser bloqueado');

        // 5.2 Conductor con licencia vencida debe arrojar ValidationException
        $driverBlocked = false;
        try {
            $validator->validateAssignment($validVehicle, $expiredDriver);
        } catch (ValidationException $e) {
            $driverBlocked = true;
            $this->assertArrayHasKey('driver_id', $e->errors());
        }
        $this->assertTrue($driverBlocked, 'El conductor con licencia vencida debe ser bloqueado');

        // 5.3 Asignación válida pasa sin excepción
        $validator->validateAssignment($validVehicle, $validDriver);

        // 6. Crear Orden de Servicio
        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $validVehicle->id,
            'driver_id' => $validDriver->id,
            'origin' => 'Sede Bancolombia Calle 72, Bogotá',
            'destination' => 'Aeropuerto El Dorado T1',
            'scheduled_start_time' => now()->addHours(2),
            'scheduled_end_time' => now()->addHours(4),
            'passengers_count' => 8,
            'status' => 'Pendiente',
        ]);
        $this->assertDatabaseHas('service_orders', ['order_number' => 'OS-2026-0001']);

        // 7. Test de Checklist Preoperacional Diario
        $checklist = PreoperationalChecklist::create([
            'vehicle_id' => $validVehicle->id,
            'driver_id' => $validDriver->id,
            'service_order_id' => $order->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'mileage' => 12050, // 50 km más
            'fluid_levels' => ['engine_oil' => true, 'brake_fluid' => true, 'coolant' => true],
            'lights_and_electrical' => ['high_beams' => true, 'low_beams' => true, 'turn_signals' => true],
            'tires_and_brakes' => ['tread_depth' => true, 'tire_pressure' => true, 'service_brake' => true],
            'safety_kit' => ['first_aid_kit' => true, 'fire_extinguisher' => true, 'road_cones' => true],
            'cabin_and_belts' => ['seatbelts' => true, 'mirrors' => true],
            'is_approved' => true,
            'driver_signature' => 'Jorge Conductor Firma Digital',
            'observations' => 'Vehículo en condiciones mecánicas óptimas',
        ]);
        $this->assertDatabaseHas('preoperational_checklists', ['id' => $checklist->id, 'is_approved' => 1]);

        // 8. Ciclo de Vida de la Orden
        // 8.1 Pasar a Asignada
        $validator->validateStatusTransition($order, 'Asignada');
        $order->update(['status' => 'Asignada']);
        $this->assertEquals('Asignada', $order->fresh()->status);

        // 8.2 Iniciar servicio (En Progreso)
        $validator->validateStatusTransition($order, 'En Progreso');
        $order->update([
            'status' => 'En Progreso',
            'actual_start_time' => now(),
            'start_mileage' => 12050,
        ]);
        $this->assertEquals('En Progreso', $order->fresh()->status);

        // 9. Novedades / Incidentes en Operación
        $incident = ServiceIncident::create([
            'service_order_id' => $order->id,
            'driver_id' => $validDriver->id,
            'vehicle_id' => $validVehicle->id,
            'incident_type' => 'Trafico',
            'description' => 'Cierre vial temporal en Av. El Dorado por mantenimiento de calzada. Retraso estimado de 15 minutos.',
            'reported_at' => now(),
            'status' => 'Abierta',
        ]);
        $this->assertDatabaseHas('service_incidents', ['id' => $incident->id, 'incident_type' => 'Trafico']);

        // 10. Finalizar Servicio
        $order->update([
            'status' => 'Finalizada',
            'actual_end_time' => now()->addHours(2),
            'end_mileage' => 12085,
        ]);
        $this->assertEquals('Finalizada', $order->fresh()->status);
        $this->assertEquals(12085, $order->fresh()->end_mileage);

        // Limpiar tenancy al finalizar prueba
        tenancy()->end();
    }
}
