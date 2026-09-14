<?php

namespace App\Services\Tenant;

use App\Models\Tenant\FuelRefill;
use App\Models\Tenant\Vehicle;

class FuelPerformanceCalculator
{
    /**
     * Calcula la distancia recorrida, rendimiento (km/galón) y precio por galón
     * para una recarga de combustible con base en el odómetro anterior.
     */
    public function computeRefillMetrics(
        Vehicle $vehicle,
        float $odometerMileage,
        float $gallons,
        float $totalCost,
        ?int $ignoreRefillId = null
    ): array {
        $pricePerGallon = $gallons > 0 ? round($totalCost / $gallons, 2) : 0.0;

        // Buscar la recarga inmediatamente anterior
        $query = FuelRefill::where('vehicle_id', $vehicle->id)
            ->where('odometer_mileage', '<', $odometerMileage)
            ->orderByDesc('odometer_mileage');

        if ($ignoreRefillId) {
            $query->where('id', '!=', $ignoreRefillId);
        }

        $lastRefill = $query->first();

        $distance = null;
        $performance = null;
        $isAnomalous = false;
        $warningMessage = null;

        if ($lastRefill && $lastRefill->odometer_mileage > 0) {
            $distance = round($odometerMileage - (float) $lastRefill->odometer_mileage, 2);

            if ($distance > 0 && $gallons > 0) {
                $performance = round($distance / $gallons, 2);

                // Detección de consumos anómalos (inferior a 10 km/gal o superior a 55 km/gal en vehículos comerciales)
                if ($performance < 12.0) {
                    $isAnomalous = true;
                    $warningMessage = "Rendimiento inusualmente bajo ({$performance} km/gal). Posible fuga o sobreconsumo mecánico.";
                } elseif ($performance > 50.0) {
                    $isAnomalous = true;
                    $warningMessage = "Rendimiento inusualmente alto ({$performance} km/gal). Verificar lectura de odómetro.";
                }
            }
        }

        return [
            'distance_since_last_refill' => $distance,
            'calculated_performance' => $performance,
            'price_per_gallon' => $pricePerGallon,
            'is_anomalous' => $isAnomalous,
            'warning_message' => $warningMessage,
        ];
    }

    /**
     * Aplica el cálculo y actualiza el odómetro del vehículo.
     */
    public function processAndSync(FuelRefill $refill): void
    {
        $metrics = $this->computeRefillMetrics(
            $refill->vehicle,
            (float) $refill->odometer_mileage,
            (float) $refill->gallons,
            (float) $refill->total_cost,
            $refill->id
        );

        $refill->updateQuietly([
            'distance_since_last_refill' => $metrics['distance_since_last_refill'],
            'calculated_performance' => $metrics['calculated_performance'],
            'price_per_gallon' => $metrics['price_per_gallon'],
        ]);

        if ($refill->vehicle && $refill->odometer_mileage > $refill->vehicle->current_mileage) {
            $refill->vehicle->update(['current_mileage' => $refill->odometer_mileage]);
        }
    }
}
