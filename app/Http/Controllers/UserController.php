<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
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
        $query = User::query();

        if ($filter = $request->input('filter')) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%");
            });
        }

        $users = $query->get();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only('filter'),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado');
    }

    public function show(User $user)
    {
        $user = User::find($user->id);

        return Inertia::render('admin/users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user = User::find($user->id);

        return Inertia::render('admin/users/Edit', [
            'user' => $user,
        ]);
    }

public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    if (empty($data['password'])) {
        unset($data['password']);
    }

    $user->update($data);

    // ✅ Redirige con flash en lugar de render
    return redirect()->route('users.edit', $user->id)
                     ->with('success', 'Los datos fueron actualizados correctamente');
}

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }
}
