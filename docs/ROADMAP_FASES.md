# Plan Maestro de Fases - sisTransporte-v2

Este documento detalla el alcance, arquitectura, componentes y estado de implementación de cada una de las fases del proyecto **sisTransporte-v2**.

---

## Índice de Fases

| Fase | Título | Rama Git | Estado |
| :---: | :--- | :--- | :---: |
| **Fase 1** | Núcleo Multi-tenant, Dominios y Autenticación Central | `feature/fase-1-core-multitenancy` | ✅ **Completada** |
| **Fase 2** | Gestión de Flota, Personal, Clientes, Aliados y Contratos | `feature/fase-2-flota-personal-clientes` | 🔄 **En Progreso** |
| **Fase 3** | Operaciones, Órdenes de Servicio y Checklist Preoperacional | `feature/fase-3-operaciones-checklist` | ⏳ Pendiente |
| **Fase 4** | Motor de Emisión FUEC Oficial (PDF + QR) y Facturación | `feature/fase-4-fuec-facturacion` | ⏳ Pendiente |
| **Fase 5** | Mantenimientos Mecánicos y Control de Combustible con Rendimiento | `feature/fase-5-mantenimiento-combustible` | ⏳ Pendiente |
| **Fase 6** | Telemetría GPS en Vivo, WebSockets (Reverb) y Ops Wallboard | `feature/fase-6-gps-telemetria-wallboard` | ⏳ Pendiente |
| **Fase 7** | API REST para App Móvil de Conductores (Sanctum) | `feature/fase-7-api-movil-conductores` | ⏳ Pendiente |
| **Fase 8** | ETL de Migración de Datos Legados, Auditoría y Producción | `feature/fase-8-migracion-produccion` | ⏳ Pendiente |

---

## Detalle de Fases

### Fase 1: Núcleo Multi-tenant, Dominios y Autenticación Central
* **Objetivo:** Establecer la infraestructura de base de datos aislada por empresa (Database per Tenant), resolución de dominios en Laravel Herd y paneles administrativos.
* **Componentes:**
  * Configuración de `stancl/tenancy v3` con prefijo `sistransporte_tenant_`.
  * Base central `sistransporte_central` para empresas (`tenants`) y dominios (`domains`).
  * Migraciones y seeders centrales y de tenant.
  * Panel SuperAdmin de Filament en `/admin` restringido a dominios centrales (`localhost`, `sistransporte-v2.test`).
  * Panel de Empresa de Filament en `/app` con inicialización dinámica por subdominio (`empresa1.sistransporte-v2.test`).
* **Pruebas Automatizadas:**
  * `Tests\Feature\TenantIsolationTest`: Verifica aislamiento de BD entre empresas.
  * `Tests\Feature\DomainResolutionTest`: Verifica resolución de dominios central y tenant.
* **Estado:** ✅ **Completada y verificada.**

---

### Fase 2: Gestión de Flota, Personal, Clientes, Aliados y Contratos
* **Objetivo:** Implementar los recursos administrativos del panel de empresa para gestionar todos los activos y actores de la operación de transporte.
* **Componentes:**
  * **Vehículos (`VehicleResource`):** Placa, tipo, capacidad, odómetro actual, badges de estado y alertas visuales automáticas para vencimientos de SOAT, Tecnomecánica, Pólizas Contractual/Extracontractual y Tarjeta de Operación.
  * **Partes y Desgaste (`VehiclePartResource` / RelationManager):** Control de kilometraje para cambio de aceite, pastillas, llantas.
  * **Conductores y Personal (`EmployeeResource`):** Cédula, datos de contacto, categoría y vigencia de licencia de conducción, foto y vinculación a usuario del sistema.
  * **Aliados Estratégicos (`PartnerResource`):** Empresas o propietarios aliados con sus vehículos y conductores vinculados.
  * **Clientes (`ClientResource`):** Segmentación Empresa vs Persona Natural, razón social, NIT y datos de facturación.
  * **Contratos de Transporte (`ContractResource`):** Número de contrato, objeto, fechas de vigencia, valor y tipo de servicio (Empresarial, Turismo, Escolar, Salud, Grupo Específico).
* **Pruebas:**
  * Validación de alertas por documentos vencidos o próximos a vencer.
  * CRUD y relaciones de vehículos con aliados y conductores.
* **Estado:** ✅ **Completada.** (Implementación de modelos, migraciones tenant, recursos Filament v5 en AppPanel y pruebas automatizadas en `FleetAndPersonnelTest`).

---

### Fase 3: Operaciones, Órdenes de Servicio y Checklist Preoperacional
* **Objetivo:** Digitalizar el ciclo de vida de los servicios de transporte y el control de seguridad vial diario.
* **Componentes:**
  * **Órdenes de Servicio (`ServiceOrderResource`):** Ciclo completo (Pendiente, Asignada, En Progreso, Finalizada, Cancelada).
  * Asignación con validación de disponibilidad de vehículo y conductor sin documentos vencidos.
  * Soporte de conductor de apoyo y vehículo de aliado.
  * **Checklist Preoperacional Diario (`PreoperationalChecklistResource`):** Inspección de fluidos, frenos, luces, equipo de carretera y firma digital del conductor antes de iniciar ruta.
  * **Novedades en Ruta (`ServiceIncidentResource`):** Reporte de incidentes mecánicos, tráfico o accidentes con soporte fotográfico.
* **Pruebas:**
  * Bloqueo de asignación si un vehículo o conductor tiene documentos vencidos.
  * Aprobación y registro de checklist diario.
* **Estado:** ✅ **Completada.** (Implementado `OperationValidationService`, `ServiceOrderResource`, `PreoperationalChecklistResource`, `ServiceIncidentResource` y suite `OperationsAndChecklistTest`).

---

### Fase 4: Motor de Emisión FUEC Oficial (PDF + QR) y Facturación
* **Objetivo:** Generación automatizada del Formato Único de Extracto de Contrato (FUEC) cumpliendo la normativa del Ministerio de Transporte de Colombia.
* **Componentes:**
  * `FuecGeneratorService`: Generación de número de 12 dígitos oficial con código de territorial, resolución y consecutivo único.
  * Generación de código QR de validación en tiempo real con `chillerlan/php-qrcode`.
  * Renderizado en PDF con diseño oficial mediante `barryvdh/laravel-dompdf`.
  * Almacenamiento seguro en disco aislado del tenant (`storage/app/tenants/{tenant_id}/fuecs/`).
  * Despacho automático del FUEC por correo electrónico al cliente mediante Jobs encolados en Redis.
  * Vinculación de número y soporte de factura a órdenes de servicio.
* **Pruebas:**
  * Verificación de consecutivo único y estructura del FUEC.
  * Generación y lectura del código QR.
* **Estado:** ✅ **Completada.** (Implementado `FuecGeneratorService`, plantilla oficial DomPDF, código QR con `chillerlan/php-qrcode`, portal de validación pública `/fuec/verify/{fuec_number}`, descarga `/fuec/download/{fuec_number}`, recurso `FuecDocumentResource` y suite `FuecGenerationTest`).

---

### Fase 5: Mantenimientos Mecánicos y Control de Combustible con Rendimiento
* **Objetivo:** Control estricto de costos operativos, mantenimiento y consumo de combustible.
* **Componentes:**
  * **Mantenimientos (`MaintenanceResource`):** Preventivos vs Correctivos, taller, costos, kilometraje y actualización de partes cambiadas.
  * **Recargas de Combustible (`FuelRefillResource`):** Registro de galones, costo, kilometraje, foto del odómetro y recibo.
  * **Calculador de Rendimiento (`FuelPerformanceCalculator`):** Cálculo automatizado de km/galón entre recargas y auditoría de desvíos anómalos de consumo.
* **Pruebas:**
  * Cálculo matemático de rendimiento por vehículo.
  * Actualización de odómetro y advertencias por inconsistencia de kilometraje.
* **Estado:** ✅ **Completada.** (Implementado `FuelPerformanceCalculator`, recursos `MaintenanceResource` y `FuelRefillResource` con odómetro automático y suite `FuelAndMaintenanceTest`).

---

### Fase 6: Telemetría GPS en Vivo, WebSockets (Reverb) y Ops Wallboard
* **Objetivo:** Monitorización de flota en tiempo real estilo centro de control.
* **Componentes:**
  * Servidor de WebSockets con **Laravel Reverb**.
  * Ingesta de telemetría GPS masiva y optimizada (`gps_locations`).
  * Mapa en tiempo real con Leaflet / Mapbox en el panel Filament (`OpsWallboard`).
  * Trazado de ruta histórica y detección de vehículos detenidos o con exceso de velocidad.
* **Pruebas:**
  * Inserción por lotes de telemetría sin latencia.
  * Emisión de eventos de ubicación vía WebSockets.
* **Estado:** ✅ **Completada.** (Implementado `GpsTelemetryService`, evento broadcast `GpsLocationReceived`, Centro de Control Wallboard interactivo con Leaflet/OpenStreetMap y KPIs en vivo `OpsWallboard`, suite `GpsTelemetryTest`).

---

### Fase 7: API REST para App Móvil de Conductores (Sanctum)
* **Objetivo:** Proporcionar los endpoints seguros para la app móvil (Ionic / Flutter / React Native).
* **Componentes:**
  * Autenticación con Laravel Sanctum resolviendo el tenant por header `X-Tenant` o subdominio.
  * Endpoints:
    * `POST /api/v1/login`
    * `GET /api/v1/orders/assigned`
    * `POST /api/v1/orders/{id}/status`
    * `POST /api/v1/checklist` (con firma en base64)
    * `POST /api/v1/fuel` (con fotos de comprobante y tablero)
    * `POST /api/v1/gps/batch` (envío periódico de coordenadas)
* **Pruebas:**
  * Tests de autenticación Sanctum y aislamiento tenant en API.
  * Ciclo de vida completo móvil: login, profile, órdenes, checklist con firma base64, novedades/incidentes, recarga combustible con rendimiento y telemetría GPS.
* **Estado:** ✅ **Completada.** (Implementado `IdentifyTenantForApi`, controladores `DriverAuthController`, `DriverOrderController`, `DriverChecklistController`, `DriverIncidentController`, `DriverFuelController`, `DriverTelemetryController`, suite `DriverApiTest` con 12 tests y 202 aserciones).

---

### Fase 8: ETL de Migración de Datos Legados, Auditoría y Producción
* **Objetivo:** Migrar los datos del sistema anterior (`c:\sisTransporte`) al nuevo modelo y preparar el despliegue en servidor.
* **Componentes:**
  * Comando Artisan `php artisan migrate:legacy-data` para extraer vehículos, empleados, clientes y contratos desde `transporteprivado` y distribuirlos en la base central y el tenant correspondiente.
  * Limpieza de datos corruptos o huérfanos.
  * Configuración de Docker de producción con Nginx, SSL Wildcard (`*.tudominio.com`) y Supervisor para colas y WebSockets.
* **Pruebas:**
  * Verificación de integridad referencial post-migración.
  * Modos de simulación `--dry-run` y ejecución real con reporte de auditoría (extraídos, importados, omitidos).
* **Estado:** ✅ **Completada.** (Implementado `LegacyDataMigrationService`, comando Artisan `php artisan migrate:legacy-data`, configuración completa de Docker de producción `docker-compose.prod.yml`, `Dockerfile`, `default.conf` Nginx con reverse proxy Reverb y Supervisor `supervisord.conf`, `.env.production.example`, suite `LegacyMigrationTest` con 3 tests y 42 aserciones).
