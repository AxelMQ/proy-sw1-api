<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);
            $username = $request->input('username');
            $password = $request->input('password');

            $user = User::where('username', $username)->first();

            if (!$user) {
                return response()->json([
                    "message" => "El username no existe."
                ], Response::HTTP_NOT_FOUND);
            }

            if (Hash::check($password, $user->password)) {
                $token = $user->createToken('token')->plainTextToken;
                $cookie = cookie('cookie_token', $token, 60 * 24);

                return response()->json([
                    "message" => "Inicio de Sesion exitoso.",
                    "token" => $token,
                    "username" => $user->username,
                ], Response::HTTP_OK)->withCookie($cookie);
            } else {
                return response()->json([
                    "message" => "Contraseña incorrecta",
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al verificar la contraseña.",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUser(Request $request)
    {
        try {
            $user = $request->user();
            $userData = $user->userData; // Asumiendo que tienes una relación definida en el modelo User

            if (!$user) {
                return response()->json([
                    "message" => "Usuario no autenticado."
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                "user" => [
                    "id" => $user->id,
                    "username" => $user->username,
                ],
                "userData" => $userData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los datos del usuario.",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
