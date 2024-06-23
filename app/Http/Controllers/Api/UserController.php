<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function allUsers()
    {
        try {
            $users = User::all();

            return response()->json([
                "message" => "Usuarios Encontrados",
                "users" => $users,
            ], Response::HTTP_OK); //200

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se encontraron Usuarios.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|unique:users',
                'password' => ['required', 'min:8', 'confirmed']
            ], [
                'username.required' => 'El username es requerido.',
                'username.unique' => 'El username ya esta en uso.',
                'password.required' => 'La contraseña es requerida.',
                'password.min' => 'La contraseña debe tener al menos :min caracteres.',
                'password.confirmed' => 'La confirmacion de la contraseña no coinciden.'
            ]);

            $user = new User();
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                "message" => "Usuario registrado exitosamente",
                "user" => $user
            ], Response::HTTP_CREATED); //201

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "message" => "no se pudo registrar el usuario. Verifique los datos proporcionados.",
                "errors" => $e->errors()
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function delete($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    "message" => "Usuario no encontrado."
                ], Response::HTTP_NOT_FOUND); //404  
            }

            $user->delete();
            return response()->json([
                "message" => "Usuario eliminado exitosamente."
            ], Response::HTTP_OK); //200

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se pudo Eliminar al Usuario.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->input('query');

            if ($query) {
                $userId = Auth::id(); // ID del usuario autenticado
                // Obtener usuarios que coinciden con la consulta
                $users = User::where('username', 'LIKE', "%{$query}%")
                    ->with('userData')
                    ->get();

                // Determinar el estado de la relación para cada usuario
                $users = $users->map(function ($user) use ($userId) {
                    if ($user->id == $userId) {
                        // Si el usuario encontrado es el mismo que el autenticado
                        $user->relationship_status = 'Mi perfil';
                    } else {
                        // Buscar una amistad existente entre el usuario autenticado y el usuario encontrado
                        $friendship = Friendship::where(function ($query) use ($userId, $user) {
                            $query->where('user_id', $userId)
                                ->where('friend_id', $user->id);
                        })->orWhere(function ($query) use ($userId, $user) {
                            $query->where('user_id', $user->id)
                                ->where('friend_id', $userId);
                        })->first();

                        // Determinar el estado de la relación basado en la existencia y estado de la amistad
                        if ($friendship) {
                            if ($friendship->estado == 'pendiente') {
                                if ($friendship->user_id == $userId) {
                                    $user->relationship_status = 'pending_sent';
                                } else {
                                    $user->relationship_status = 'pending_received';
                                }
                            } else {
                                $user->relationship_status = $friendship->estado;
                            }
                        } else {
                            $user->relationship_status = 'no_relation';
                        }
                    }
                    $user->friends_count = $user->friends()->count();
                    return $user;
                });

                return response()->json([
                    "message" => "Usuario encontrado.",
                    "users" => $users,
                ], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'No search query provided.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logró buscar al usuario, inténtelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
