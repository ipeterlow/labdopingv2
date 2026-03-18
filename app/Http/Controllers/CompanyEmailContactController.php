<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyEmailContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyEmailContactController extends Controller
{
    /**
     * Listado de contactos de correo por empresa con filtros y paginación.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 25);
        $search = $request->input('search');
        $companyId = $request->input('company_id');
        $isActive = $request->input('is_active');

        $query = CompanyEmailContact::query()
            ->with('company')
            ->orderBy('company_id')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($isActive !== null && $isActive !== '') {
            if ($isActive === '1' || $isActive === 1 || $isActive === true || $isActive === 'true') {
                $query->where('is_active', true);
            } elseif ($isActive === '0' || $isActive === 0 || $isActive === false || $isActive === 'false') {
                $query->where('is_active', false);
            }
        }

        $contacts = $query->paginate($perPage)->withQueryString();

        $companies = Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('contactemails/Index', [
            'contacts' => $contacts->items(),
            'pagination' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
                'from' => $contacts->firstItem(),
                'to' => $contacts->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'is_active' => $isActive,
                'per_page' => $perPage,
            ],
            'companies' => $companies,
        ]);
    }

    /**
     * Crear un nuevo contacto de correo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        CompanyEmailContact::create($validated);

        return redirect()
            ->route('companyemailcontacts.index')
            ->with('success', 'Contacto de correo creado correctamente.');
    }

    /**
     * Actualizar un contacto existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $contact = CompanyEmailContact::findOrFail($id);

        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $contact->update($validated);

        return redirect()
            ->route('companyemailcontacts.index')
            ->with('success', 'Contacto de correo actualizado correctamente.');
    }

    /**
     * Eliminar un contacto.
     */
    public function destroy(int $id): RedirectResponse
    {
        $contact = CompanyEmailContact::findOrFail($id);
        $contact->delete();

        return redirect()
            ->route('companyemailcontacts.index')
            ->with('success', 'Contacto de correo eliminado correctamente.');
    }
}
