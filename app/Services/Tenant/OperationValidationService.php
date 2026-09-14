<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Employee;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Illuminate\Validation\ValidationException;

class OperationValidationService
{
    /**
     * Validar que tanto el vehículo como los conductores sean elegibles para prestar servicio.
     *
     * @throws ValidationException
     */
    public function validateAssignment(?Vehicle $vehicle, ?Employee $driver, ?Employee $supportDriver = null): void
    {
        $errors = [];

        if ($vehicle) {
            $vehicleErrors = $vehicle->getEligibilityErrors();
            if (! empty($vehicleErrors)) {
                $errors['vehicle_id'] = [
                    "El vehículo {$vehicle->plate} no puede ser asignado: " . implode(' ', $vehicleErrors),
                ];
            }
        }

        if ($driver) {
            $driverErrors = $driver->getEligibilityErrors();
            if (! empty($driverErrors)) {
                $errors['driver_id'] = [
                    "El conductor {$driver->name} no puede ser asignado: " . implode(' ', $driverErrors),
                ];
            }
        }

        if ($supportDriver) {
            $supportErrors = $supportDriver->getEligibilityErrors();
            if (! empty($supportErrors)) {
                $errors['support_driver_id'] = [
                    "El conductor de apoyo {$supportDriver->name} no puede ser asignado: " . implode(' ', $supportErrors),
                ];
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Valida el avance de estado de la orden de servicio.
     *
     * @throws ValidationException
     */
    public function validateStatusTransition(ServiceOrder $order, string $newStatus): void
    {
        if ($newStatus === 'Asignada' || $newStatus === 'En Progreso') {
            if (! $order->vehicle_id) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'No se puede poner la orden en ' . $newStatus . ' sin un vehículo asignado.',
                ]);
            }

            if (! $order->driver_id) {
                throw ValidationException::withMessages([
                    'driver_id' => 'No se puede poner la orden en ' . $newStatus . ' sin un conductor asignado.',
                ]);
            }

            $this->validateAssignment($order->vehicle, $order->driver, $order->supportDriver);
        }

        if ($newStatus === 'En Progreso') {
            // Verificar si tiene al menos un checklist preoperacional aprobado para hoy
            $hasChecklist = $order->checklists()
                ->where('date', now()->toDateString())
                ->where('is_approved', true)
                ->exists();

            if (! $hasChecklist && $order->vehicle) {
                // También verificamos si el vehículo tiene un checklist aprobado hoy en general
                $vehicleChecklist = $order->vehicle->checklists()
                    ->where('date', now()->toDateString())
                    ->where('is_approved', true)
                    ->exists();

                if (! $vehicleChecklist) {
                    throw ValidationException::withMessages([
                        'status' => 'No se puede iniciar el servicio sin un Checklist Preoperacional aprobado hoy para el vehículo ' . $order->vehicle->plate . '.',
                    ]);
                }
            }
        }
    }
}
