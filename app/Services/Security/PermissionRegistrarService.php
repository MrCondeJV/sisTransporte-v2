<?php

namespace App\Services\Security;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRegistrarService
{
    /**
     * Catálogo maestro de permisos organizados por módulo de transporte.
     *
     * @return array<string, array<string, string>>
     */
    public static function getModulePermissions(): array
    {
        return [
            'Operaciones & Órdenes de Servicio' => [
                'ordenes.ver' => 'Consultar listado y ficha técnica de órdenes de servicio',
                'ordenes.crear' => 'Crear y programar nuevas órdenes de servicio (ODS)',
                'ordenes.editar' => 'Modificar itinerarios, pasajeros, conductores y tarifas',
                'ordenes.eliminar' => 'Eliminar órdenes de servicio no ejecutadas',
                'ordenes.cambiar_estado' => 'Avanzar estado del servicio (En progreso, Completado)',
                'ordenes.anular' => 'Solicitar o aprobar anulación de servicios',
                'fuec.emitir' => 'Generar y emitir planillas FUEC digitales oficiales',
            ],
            'Flota Vehicular & Equipos' => [
                'vehiculos.ver' => 'Consultar parque automotor y hojas de vida de vehículos',
                'vehiculos.crear' => 'Vincular nuevos vehículos a la flota empresarial',
                'vehiculos.editar' => 'Actualizar datos mecánicos, SOAT, RTM y pólizas contractuales',
                'vehiculos.eliminar' => 'Desvincular o retirar vehículos del sistema',
            ],
            'Personal & Conductores' => [
                'conductores.ver' => 'Consultar directorio de conductores y talento humano',
                'conductores.crear' => 'Registrar nuevos conductores y empleados',
                'conductores.editar' => 'Actualizar datos personales, licencias de conducción y EPS',
                'conductores.eliminar' => 'Inactivar o eliminar personal de la nómina operativa',
            ],
            'Comercial & Clientes' => [
                'clientes.ver' => 'Consultar catálogo de clientes corporativos y particulares',
                'clientes.crear' => 'Crear nuevas empresas contratantes o clientes directos',
                'clientes.editar' => 'Actualizar datos fiscales, NIT, direcciones y contactos',
                'clientes.eliminar' => 'Eliminar o suspender cuentas de clientes',
            ],
            'Contratos de Transporte' => [
                'contratos.ver' => 'Consultar contratos de prestación de servicio suscritos',
                'contratos.crear' => 'Registrar nuevos contratos empresariales, escolares o turismo',
                'contratos.editar' => 'Modificar vigencias, pólizas asociadas y cláusulas',
                'contratos.eliminar' => 'Cancelar o archivar contratos de transporte',
            ],
            'Catálogo de Rutas Frecuentes' => [
                'rutas.ver' => 'Consultar mapa y tarifario de rutas frecuentes',
                'rutas.crear' => 'Registrar nuevos trayectos origen - destino',
                'rutas.editar' => 'Ajustar distancias, tiempos estimados y tarifas sugeridas',
                'rutas.eliminar' => 'Eliminar rutas obsoletas del catálogo',
            ],
            'Aliados & Convenios de Flota' => [
                'aliados.ver' => 'Consultar empresas y propietarios aliados en convenio',
                'aliados.crear' => 'Vincular nuevos aliados estratégicos',
                'aliados.editar' => 'Actualizar datos de convenios y comisiones',
                'aliados.eliminar' => 'Desvincular aliados del sistema',
            ],
            'Combustible & Rendimiento' => [
                'combustible.ver' => 'Consultar bitácora de tanqueos y rendimientos de galonaje',
                'combustible.crear' => 'Registrar nuevos consumos y recibos de EDS',
                'combustible.editar' => 'Modificar montos, kilometrajes o proveedores de combustible',
                'combustible.eliminar' => 'Anular registros de combustible erróneos',
            ],
            'Mantenimiento de Flota' => [
                'mantenimientos.ver' => 'Consultar órdenes de taller y mantenimientos preventivos/correctivos',
                'mantenimientos.crear' => 'Registrar ingresos a taller e intervenciones mecánicas',
                'mantenimientos.editar' => 'Actualizar costos de repuestos, mano de obra y diagnósticos',
                'mantenimientos.eliminar' => 'Eliminar registros de mantenimiento',
            ],
            'Inspección Preoperacional (PESV)' => [
                'checklists.ver' => 'Consultar auditoría de inspecciones diarias y firmas digitales',
                'checklists.crear' => 'Diligenciar inspección preoperacional técnico-mecánica',
                'checklists.revisar' => 'Auditar y descargar certificaciones preoperacionales en PDF',
            ],
            'Reportes Gerenciales & Finanzas' => [
                'reportes_gerenciales.ver' => 'Acceso al panel ejecutivo de KPIs, gráficos y calendario',
                'reportes_gerenciales.exportar' => 'Exportar gráficos, balances y órdenes a Excel',
                'reportes_gerenciales.cartera' => 'Auditar órdenes pagadas, pendientes de pago y facturación',
                'gerencial.aprobaciones' => 'Gestionar y autorizar solicitudes de anulación y cambios de tarifas',
            ],
            'Seguridad, Usuarios & Roles' => [
                'usuarios.ver' => 'Consultar lista de usuarios con acceso al sistema',
                'usuarios.crear' => 'Crear nuevas cuentas de usuario del sistema',
                'usuarios.editar' => 'Modificar credenciales, estado activo/inactivo y roles',
                'usuarios.eliminar' => 'Eliminar o suspender usuarios',
                'roles.ver' => 'Consultar roles de seguridad del sistema',
                'roles.crear' => 'Crear nuevos roles personalizados',
                'roles.editar' => 'Configurar y sincronizar la matriz de permisos de cada rol',
                'roles.eliminar' => 'Eliminar roles personalizados',
            ],
        ];
    }

    /**
     * Sincronizar todos los permisos del catálogo en la base de datos del tenant actual.
     */
    public static function syncTenantPermissions(): void
    {
        $modules = self::getModulePermissions();

        foreach ($modules as $permissions) {
            foreach ($permissions as $permissionName => $description) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
            }
        }

        // Asignar todos los permisos al rol Administrador si existe
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->syncPermissions($allPermissions);
        }
    }
}
