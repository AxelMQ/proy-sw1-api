<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function allUsers(){
        try{
            $users = User::all();
            
            return response()->json([
                "message" => "Usuarios Encontrados",
                "users" => $users,
            ], Response::HTTP_OK); //200

        } catch (\Exception $e){
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

    public function delete($id){
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
}
