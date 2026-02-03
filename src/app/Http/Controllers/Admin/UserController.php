<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);
        
        // Enviar email de verificación
        $user->sendEmailVerificationNotification();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente. Se ha enviado un correo de verificación.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Un admin no puede editar a otro admin (excepto a sí mismo)
        if ($user->hasRole('admin') && $user->id !== auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes editar la información de otro administrador.');
        }

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Un admin no puede actualizar a otro admin (excepto a sí mismo)
        if ($user->hasRole('admin') && $user->id !== auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes modificar la información de otro administrador.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sincronizar roles
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Deactivate or reactivate the specified user.
     */
    public function destroy(User $user)
    {
        // Prevenir auto-desactivación si es admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        // Prevenir desactivación de otros administradores
        if ($user->hasRole('admin')) {
            return back()->with('error', 'No puedes desactivar la cuenta de un administrador.');
        }

        // Verificar si el usuario está desactivado para reactivarlo o desactivarlo
        if ($user->isDisabled()) {
            $user->update(['disabled_at' => null]);
            $message = 'Usuario activado exitosamente.';
        } else {
            $user->update(['disabled_at' => now()]);
            $message = 'Usuario desactivado exitosamente.';
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
