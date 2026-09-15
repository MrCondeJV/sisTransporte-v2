<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Client;
use App\Models\Tenant\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    /**
     * Listado de Contratos de Transporte.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $query = Contract::with('client')->orderByDesc('start_date');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('contract_number', 'like', "%{$term}%")
                    ->orWhere('contract_object', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($cq) use ($term) {
                        $cq->where('business_name', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('document_number', 'like', "%{$term}%");
                    });
            });
        }

        $contratos = $query->paginate(15)->withQueryString();
        $totalContratos = Contract::count();
        $contratosActivos = Contract::where('status', 'Vigente')->count();

        return view('contratos.index', compact('contratos', 'term', 'totalContratos', 'contratosActivos'));
    }

    public function create(): View
    {
        $clientes = Client::where('status', 'Activo')->orderBy('business_name')->get();

        return view('contratos.create', compact('clientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contract_number' => ['required', 'string', 'max:50', 'unique:contracts,contract_number'],
            'contract_object' => ['required', 'string', 'max:500'],
            'contract_type' => ['required', 'string', 'in:Empresarial,Escolar,Turismo,Salud,Grupo Especifico'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:Vigente,Vencido,Cancelado'],
        ]);

        $contrato = Contract::create($validated);

        return redirect()->route('contratos.index')
            ->with('success', "Contrato N° '{$contrato->contract_number}' registrado exitosamente.");
    }

    public function show(Contract $contrato): View
    {
        $contrato->load(['client', 'serviceOrders.vehicle', 'serviceOrders.driver']);

        return view('contratos.show', compact('contrato'));
    }

    public function edit(Contract $contrato): View
    {
        $clientes = Client::orderBy('business_name')->get();

        return view('contratos.edit', compact('contrato', 'clientes'));
    }

    public function update(Request $request, Contract $contrato): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contract_number' => ['required', 'string', 'max:50', 'unique:contracts,contract_number,'.$contrato->id],
            'contract_object' => ['required', 'string', 'max:500'],
            'contract_type' => ['required', 'string', 'in:Empresarial,Escolar,Turismo,Salud,Grupo Especifico'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:Vigente,Vencido,Cancelado'],
        ]);

        $contrato->update($validated);

        return redirect()->route('contratos.index')
            ->with('success', "Contrato N° '{$contrato->contract_number}' actualizado correctamente.");
    }

    public function destroy(Contract $contrato): RedirectResponse
    {
        $numero = $contrato->contract_number;
        $contrato->delete();

        return redirect()->route('contratos.index')
            ->with('success', "Contrato N° '{$numero}' eliminado del sistema.");
    }
}
