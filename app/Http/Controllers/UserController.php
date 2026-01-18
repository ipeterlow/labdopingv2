<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(HandlePrecognitiveRequests::class)->only('store');
    }

    public function index(Request $request)
    {
        $query = User::query()->with('currentTeam');

        if ($filter = $request->input('filter')) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%");
            });
        }

        $users = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company' => $user->currentTeam ? ['id' => $user->currentTeam->id, 'name' => $user->currentTeam->name] : null,
            ];
        });

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only('filter'),
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        $companies = \App\Models\Company::all(['id', 'name']);

        return Inertia::render('admin/users/Create', [
            'roles' => $roles,
            'companies' => $companies,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
            'current_team_id' => $request->input('current_team_id'),
        ]);

        // Asignar roles al usuario
        if ($request->has('roles') && is_array($request->roles)) {
            $user->syncRoles($request->roles);
        }

        return to_route('users.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'currentTeam']);

        return Inertia::render('admin/users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles,
                'company' => $user->currentTeam ? ['id' => $user->currentTeam->id, 'name' => $user->currentTeam->name] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        $companies = \App\Models\Company::all(['id', 'name']);

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('id')->toArray(),
                'current_team_id' => $user->current_team_id,
            ],
            'roles' => $roles,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'current_team_id' => 'nullable|exists:companies,id',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $user->update($data);

        // Actualizar roles del usuario
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        } else {
            $user->syncRoles([]);
        }

        // ✅ Redirige con flash en lugar de render
        return to_route('users.edit', $user->id)
            ->with('success', 'Los datos fueron actualizados correctamente');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }
}
