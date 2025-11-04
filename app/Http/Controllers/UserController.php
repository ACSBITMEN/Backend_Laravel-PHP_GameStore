<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // ← AGREGAR
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Solo admin y manager pueden ver usuarios
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $query = User::with('role')->active();

        // Filtros
        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('role')) {
            $query->byRole($request->role);
        }

        $users = $query->paginate(10);

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * createUser a newly created resource.
     */
    public function createUser(Request $request)
    {
        $currentUser = $request->user();
        
        // Solo admin y manager pueden crear usuarios
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Restricciones de roles según el usuario actual
        $requestedRoleId = $request->role_id;
        
        if ($currentUser->isAdmin()) {
            // Admin no puede crear managers ni otros admins
            $requestedRole = \App\Models\Role::find($requestedRoleId);
            if ($requestedRole->name === 'manager' || $requestedRole->name === 'admin') {
                return response()->json([
                    'message' => 'Admins can only create customer users'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'country' => $request->country,
            'role_id' => $requestedRoleId,
            'status' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('role')
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $currentUser = $request->user();
        $targetUser = User::with('role')->find($id);
        
        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], Response::HTTP_FORBIDDEN);
        }
        
        // Verificar permisos
        if (!$currentUser->isAdmin() && !$currentUser->isManager() && $currentUser->id !== $targetUser->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'user' => $targetUser
        ]);
    }

    /**
    * Ver perfil del usuario actual (ruta /profile)
    */
    public function showProfile(Request $request)
    {
        $user = $request->user()->load('role');
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'avatar' => $user->avatar,
                'role' => $user->role->name,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ]
        ]);
    }
    /**
     * Update user profile (current user)
     */
    public function updateProfile(Request $request)  // ← QUITAR User $user
    {
        $user = $request->user();  // ← Obtener usuario actual
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'avatar' => 'nullable|string|max:255',
            // ← QUITAR role_id y status - usuario no puede cambiar estos
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update($request->all());

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('role')
        ]);
    }
    /**
     * Update other users (admin/manager only)
     */
    public function updateUser(Request $request, User $user)
    {
        $currentUser = $request->user();
        
        // Verificar que es admin o manager
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'avatar' => 'nullable|string|max:255',
            'role_id' => 'sometimes|required|exists:roles,id',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // RESTRICCIONES DE ROLES:
        
        // Si es ADMIN, no puede editar Managers ni otros Admins
        if ($currentUser->isAdmin()) {
            $targetUserRole = $user->role->name;
            if ($targetUserRole === 'manager' || $targetUserRole === 'admin') {
                return response()->json([
                    'message' => 'Admins can only edit customer users'
                ], Response::HTTP_FORBIDDEN);
            }
            
            // ✅ SOLO MANAGER PUEDE CAMBIAR ROLES - Admin NO puede cambiar roles
            if ($request->has('role_id')) {
                return response()->json([
                    'message' => 'Only managers can change user roles'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Si se intenta cambiar role_id, verificar que sea Manager
        if ($request->has('role_id') && !$currentUser->isManager()) {
            return response()->json([
                'message' => 'Only managers can change user roles'
            ], Response::HTTP_FORBIDDEN);
        }

        // MANAGER puede editar cualquier usuario y asignar cualquier rol

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('role')
        ]);
    }
    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();
        
        // Solo manager puede eliminar usuarios
        if (!$currentUser->isManager()) {
            return response()->json([
                'message' => 'Unauthorized. Only managers can delete users.'
            ], Response::HTTP_FORBIDDEN);
        }

        // No permitir auto-eliminación
        if ($currentUser->id === $user->id) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], Response::HTTP_FORBIDDEN);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Deactivate user (soft delete alternative)
     */
    public function deactivate(Request $request, User $user)
    {
        $currentUser = $request->user();
        
        // Solo admin y manager pueden desactivar usuarios
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $user->updateUser(['status' => false]);

        return response()->json([
            'message' => 'User deactivated successfully'
        ]);
    }

    /**
     * Activate user
     */
    public function activate(Request $request, User $user)
    {
        $currentUser = $request->user();
        
        // Solo admin y manager pueden activar usuarios
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $user->updateUser(['status' => true]);

        return response()->json([
            'message' => 'User activated successfully'
        ]);
    }
}