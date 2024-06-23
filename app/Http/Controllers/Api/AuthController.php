<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    "message" => "Usuario no autenticado"
                ], Response::HTTP_UNAUTHORIZED); //401
            }

            $user = User::with('userData')->findOrFail($userId);

            // Contar los amigos aceptados
            $friendCount = Friendship::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('friend_id', $userId);
            })
                ->where('estado', 'aceptado')
                ->count();

            return response()->json([
                "user" => [
                    "id" => $user->id,
                    "username" => $user->username,
                    "friends_count" =>   $user->friends_count = $user->friends()->count(), // Incluir el conteo de amigos
                ],
                "userData" => $user->userData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los datos del usuario.",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
