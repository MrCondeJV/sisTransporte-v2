<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Catálogo de Clientes Corporativos y Particulares.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $query = Client::with('contracts')->orderBy('business_name');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('business_name', 'like', "%{$term}%")
                    ->orWhere('document_number', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $clientes = $query->paginate(15)->withQueryString();
        $totalClientes = Client::count();

        return view('clientes.index', compact('clientes', 'term', 'totalClientes'));
    }

    public function create(): View
    {
        return view('clientes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:Empresa,Persona Natural'],
            'business_name' => ['nullable', 'string', 'max:200'],
            'document_number' => ['required', 'string', 'max:30'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $cliente = Client::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->display_name}' registrado exitosamente.");
    }

    public function show(Client $cliente): View
    {
        $cliente->load(['contracts', 'serviceOrders.vehicle', 'serviceOrders.driver']);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Client $cliente): View
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Client $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:Empresa,Persona Natural'],
            'business_name' => ['nullable', 'string', 'max:200'],
            'document_number' => ['required', 'string', 'max:30'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Activo,Inactivo'],
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$cliente->display_name}' actualizado correctamente.");
    }

    public function destroy(Client $cliente): RedirectResponse
    {
        $nombre = $cliente->display_name;
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', "Cliente '{$nombre}' eliminado del sistema.");
    }
}
