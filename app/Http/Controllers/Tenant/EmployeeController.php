<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Padrón de Conductores & Personal Operativo.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $query = Employee::with('vehicles')->orderBy('name');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('document_number', 'like', "%{$term}%")
                    ->orWhere('driver_license_number', 'like', "%{$term}%");
            });
        }

        $conductores = $query->paginate(15)->withQueryString();
        $totalConductores = Employee::count();

        $limite30Dias = now()->addDays(30)->toDateString();
        $licenciasPorVencer = Employee::where('driver_license_expiration', '<=', $limite30Dias)->count();

        return view('conductores.index', compact('conductores', 'term', 'totalConductores', 'licenciasPorVencer'));
    }

    /**
     * Formulario de creación de conductor/empleado.
     */
    public function create(): View
    {
        $aliados = Partner::where('status', 'Activo')->orderBy('name')->get();

        return view('conductores.create', compact('aliados'));
    }

    /**
     * Almacenar un nuevo conductor/empleado.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'document_number' => ['required', 'string', 'max:30', 'unique:employees,document_number'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'employee_type' => ['required', 'in:Conductor,Administrativo,Operativo'],
            'contract_number' => ['nullable', 'string', 'max:50'],
            'contract_type' => ['nullable', 'in:Termino Fijo,Termino Indefinido,Prestacion Servicios,Otro'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'driver_license_number' => ['nullable', 'string', 'max:50'],
            'driver_license_category' => ['nullable', 'string', 'max:10'],
            'driver_license_expiration' => ['nullable', 'date'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $conductor = Employee::create($validated);

        return redirect()->route('conductores.index')
            ->with('success', "Conductor '{$conductor->name}' registrado exitosamente.");
    }

    /**
     * Hoja de vida y ficha técnica del conductor.
     */
    public function show(Employee $conductore): View
    {
        $conductore->load(['vehicles', 'partner', 'serviceOrders.client', 'preoperationalChecklists.vehicle']);

        return view('conductores.show', compact('conductore'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Employee $conductore): View
    {
        $aliados = Partner::where('status', 'Activo')->orderBy('name')->get();

        return view('conductores.edit', compact('conductore', 'aliados'));
    }

    /**
     * Actualizar datos del conductor.
     */
    public function update(Request $request, Employee $conductore): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'document_number' => ['required', 'string', 'max:30', 'unique:employees,document_number,'.$conductore->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'employee_type' => ['required', 'in:Conductor,Administrativo,Operativo'],
            'contract_number' => ['nullable', 'string', 'max:50'],
            'contract_type' => ['nullable', 'in:Termino Fijo,Termino Indefinido,Prestacion Servicios,Otro'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'driver_license_number' => ['nullable', 'string', 'max:50'],
            'driver_license_category' => ['nullable', 'string', 'max:10'],
            'driver_license_expiration' => ['nullable', 'date'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $conductore->update($validated);

        return redirect()->route('conductores.index')
            ->with('success', "Datos de '{$conductore->name}' actualizados correctamente.");
    }

    /**
     * Eliminar (soft delete) conductor.
     */
    public function destroy(Employee $conductore): RedirectResponse
    {
        $nombre = $conductore->name;
        $conductore->delete();

        return redirect()->route('conductores.index')
            ->with('success', "Conductor '{$nombre}' retirado del sistema.");
    }
}
