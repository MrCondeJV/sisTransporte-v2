# sisTransporte v2 (SaaS Multi-tenant Multi-Database)

Sistema integral de gestión para empresas de **transporte especial y privado de pasajeros** (regulado en Colombia), desarrollado con **Laravel 11**, **stancl/tenancy v3** (arquitectura de base de datos aislada por empresa) y **Laravel Filament v3**.

---

## 🏛️ Arquitectura del Sistema

El proyecto opera bajo el modelo **Database per Tenant** (una base de datos física independiente por cada empresa de transporte cliente), garantizando aislamiento absoluto de datos, seguridad y cumplimiento normativo.

```
┌─────────────────────────────────┐
│     MySQL 8 (127.0.0.1:3306)    │
└────────────────┬────────────────┘
                 │
        ┌────────┴──────────────────────────┐
        │                                   │
┌───────▼──────────────────────────┐  ┌─────▼──────────────────────────────┐
│  sistransporte_central           │  │  sistransporte_tenant_{id}          │
│  (Base Central de la Plataforma) │  │  (Base de Datos Aislada por Empresa)│
├──────────────────────────────────┤  ├────────────────────────────────────┤
│ • tenants (empresas clientes)    │  │ • users (usuarios y conductores)   │
│ • domains (subdominios mapeados) │  │ • roles & permissions (RBAC)       │
│ • users (superadministradores)   │  │ • vehicles (flota y odómetro)      │
│ • jobs, cache                    │  │ • employees (conductores y personal│
└──────────────────────────────────┘  │ • contracts, clients & partners    │
                                      │ • service_orders (viajes y rutas)  │
                                      │ • preoperational_checklists        │
                                      │ • fuec_documents (PDF con QR)      │
                                      │ • maintenances & fuel_refills      │
                                      │ • gps_locations (telemetría en vivo│
                                      └────────────────────────────────────┘
```

---

## 🚀 Requisitos del Entorno

* **PHP:** 8.3 o 8.4 (probado y optimizado con Laravel Herd en Windows).
* **Gestor de Dependencias:** Composer 2.x.
* **Base de Datos:** MySQL 8.0+.
* **Servidor Local Recomendado:** [Laravel Herd](https://herd.laravel.com/).
  * Soporte nativo para dominios `.test` y subdominios comodín (`*.sistransporte-v2.test`).

---

## ⚙️ Instalación y Puesta en Marcha

### 1. Clonar el repositorio
```bash
git clone https://github.com/MrCondeJV/sisTransporte-v2.git
cd sisTransporte-v2
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Configurar variables de entorno
Copia el archivo `.env.example` a `.env`:
```bash
cp .env.example .env
php artisan key:generate
```
Asegúrate de que la conexión de base de datos apunte a tu servidor MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistransporte_central
DB_USERNAME=root
DB_PASSWORD=root
APP_URL=http://sistransporte-v2.test
```

### 4. Ejecutar migraciones y seeders
```bash
# Migraciones de la base de datos central y creación de la empresa demo
php artisan migrate --seed

# Migraciones para todas las bases de datos de empresas (tenants)
php artisan tenants:migrate

# Seeders para las empresas (roles, usuario admin y conductor de prueba)
php artisan tenants:seed
```

---

## 🔑 Accesos y Credenciales de Prueba

### 1. Panel Central (SuperAdmin de la Plataforma)
Permite crear empresas, configurar subdominios y supervisar el estado de las bases de datos.
* **URL:** [http://sistransporte-v2.test/admin](http://sistransporte-v2.test/admin)
* **Email:** `admin@sistransporte.com`
* **Contraseña:** `admin123456`

### 2. Panel Operativo de la Empresa Demo (`empresa1`)
Panel de administración de la empresa conectada a su base de datos física `sistransporte_tenant_empresa1`.
* **URL:** [http://empresa1.sistransporte-v2.test/app](http://empresa1.sistransporte-v2.test/app)
* **Email:** `admin@empresa.com`
* **Contraseña:** `admin123456`

---

## 🧪 Ejecución de Pruebas Automatizadas (PHPUnit)

Para correr la suite completa de pruebas:
```bash
php artisan test
```

Para probar específicamente el aislamiento de bases de datos entre tenants:
```bash
php artisan test --filter=TenantIsolationTest
```

Para probar la resolución de dominios en Laravel Herd:
```bash
php artisan test --filter=DomainResolutionTest
```

Para probar el control de flota, conductores y alertas de vencimiento (Fase 2):
```bash
php artisan test --filter=FleetAndPersonnelTest
```

Para probar operaciones, ciclo de vida de órdenes, checklist preoperacional y bloqueo normativo (Fase 3):
```bash
php artisan test --filter=OperationsAndChecklistTest
```

Para probar la emisión de FUEC oficial, generación de PDF, código QR y verificación pública (Fase 4):
```bash
php artisan test --filter=FuecGenerationTest
```

Para probar el control de mantenimientos, costos y rendimiento de combustible km/galón (Fase 5):
```bash
php artisan test --filter=FuelAndMaintenanceTest
```

Para probar la telemetría GPS, georreferenciación y eventos de broadcast (Fase 6):
```bash
php artisan test --filter=GpsTelemetryTest
```

---

## 📋 Hoja de Ruta del Proyecto

El desarrollo está organizado en 8 fases documentadas en detalle en [`docs/ROADMAP_FASES.md`](docs/ROADMAP_FASES.md):

* **Fase 1:** Núcleo Multi-tenant, Dominios y Autenticación Central *(✅ Completada)*.
* **Fase 2:** Gestión de Flota, Personal, Clientes, Aliados y Contratos *(✅ Completada)*.
* **Fase 3:** Operaciones, Órdenes de Servicio y Checklist Preoperacional *(✅ Completada)*.
* **Fase 4:** Motor de Emisión FUEC Oficial (PDF + QR) y Facturación *(✅ Completada)*.
* **Fase 5:** Mantenimientos Mecánicos y Control de Combustible con Rendimiento *(✅ Completada)*.
* **Fase 6:** Telemetría GPS en Vivo, WebSockets (Reverb) y Ops Wallboard *(✅ Completada)*.
* **Fase 7:** API REST para App Móvil de Conductores (Sanctum) *(🔄 Próxima)*.
* **Fase 8:** ETL de Migración de Datos Legados, Auditoría y Producción.
