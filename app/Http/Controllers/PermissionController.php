<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $permissions = Permission::select('id', 'name', 'created_at')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->get();

        return Inertia::render('admin/permission/Index', [
            'permissions' => $permissions,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/permission/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'permiso' => ['required', 'string', 'max:150', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $validated['permiso']]);

        return redirect()->route('permissions.create')->with('success', 'Permiso creado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        //
        $permission = Permission::find($permission->id);

        return Inertia::render('admin/permission/Show', [
            'permission' => $permission,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        //
        $permission = Permission::find($permission->id);

        return Inertia::render('admin/permission/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        //
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name,'.$permission->id],
        ]);
        $permission->name = $validated['name'];
        $permission->save();

        return redirect()->route('permissions.edit', $permission->id)->with('success', 'Permiso actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
