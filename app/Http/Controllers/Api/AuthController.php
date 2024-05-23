<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                $cookie = cookie('cookie_token', $token, 60*24);

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

        } catch (\Exception $e)  {
            return response()->json([
                "message" => "Error al verificar la contraseña.",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
