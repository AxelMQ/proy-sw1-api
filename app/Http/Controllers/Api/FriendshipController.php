<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FriendshipController extends Controller
{
    //
    public function sendSolicitud($friend_id)
    {
        try {
            Friendship::create([
                'user_id' => Auth::id(),
                'friend_id' => $friend_id,
                'estado' => 'pendiente',
            ]);

            return response()->json([
                'message' => 'Solicitud de amistad enviada.'
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No logro enviar la solictud, intentelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function acceptSolicitud($friendId)
    {
        try {

            $userId = Auth::id();
            $friendship = Friendship::where('user_id', $friendId)
                ->where('friend_id', $userId)
                ->firstOrFail();
            $friendship->estado = 'aceptado';
            $friendship->save();

            return response()->json([
                "message" => "Solicitud de amistad aceptada."
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro aceptar la solicitud, intentelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function rechazadoSolicitud($friendId)
    {
        try {
            $userId = Auth::id();
            $friendship = Friendship::where('user_id', $friendId)
                ->where('friend_id', $userId)
                ->firstOrFail();

            $friendship->estado = 'rechazado';
            $friendship->save();

            return response()->json([
                "message" => "Solicitud de amistad rechazada."
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro rechazar la solicitud, intentelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteFriend($id)
    {
        try {
            $friendship = Friendship::where(function ($query) use ($id) {
                $query->where('user_id', Auth::id())
                    ->where('friend_id', $id)
                    ->orWhere('user_id', $id)
                    ->where('friend_id', Auth::id());
            })
                ->where('estado', 'aceptado')
                ->firstOrFail();

            $friendship->delete();

            return response()->json([
                "message" => "Amistad eliminada.",
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro eliminar la amistad, intentelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function cancelarSolicitud($friendId)
    {
        try {
            $userId = Auth::id();
            $friendship = Friendship::where('user_id', $userId)
                ->where('friend_id', $friendId)
                ->where('estado', 'pendiente')
                ->firstOrFail();


            $friendship->delete();

            return response()->json([
                "message" => "Solicitud de amistad cancelada.",
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Solicitud de amistad no encontrada",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logró cancelar la solicitud, inténtelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function getSolicitudes()
    {
        try {
            $userId = Auth::id();
            // Verificar que Auth::id() está devolviendo un valor válido
            if (!$userId) {
                return response()->json([
                    "message" => "Usuario no autenticado"
                ], Response::HTTP_UNAUTHORIZED); //401
            }

            $solicitudes = Friendship::where('friend_id', $userId)
                ->where('estado', 'pendiente')
                ->with(['user.userData'])
                ->get();


            // Mapear las solicitudes para incluir el estado de la relación y los datos del usuario
            $solicitudes = $solicitudes->map(function ($solicitud) use ($userId) {
                $user = $solicitud->user;
                $user->relationship_status = 'pending_received';
                $user->friends_count = $user->friends()->count();
                return $user;
            });

            return response()->json([
                "message" => "Solicitudes de Amistad, encontradas.",
                "solicitudes" => $solicitudes
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log::error('Model not found: ' . $e->getMessage());
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404

        } catch (\Exception $e) {
            // Log::error('Error fetching friend requests: ' . $e->getMessage());
            return response()->json([
                "message" => "No se logro obtener las solicitudes de amistad, intentelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    // public function listFriends($id)
    // {
    //     try {
    //         $user = User::findOrFail($id);

    //         $friendships = Friendship::where(function ($query) use ($user) {
    //             $query->where('user_id', $user->id)
    //                 ->orWhere('friend_id', $user->id);
    //         })
    //             ->where('estado', 'aceptado')
    //             ->with(['user.userData', 'friend.userData'])
    //             ->get();


    //         $friendList = $friendships->map(function ($friendship) use ($user) {
    //             // Obtener el amigo del usuario actual
    //             $friend = $friendship->user_id == $user->id ? $friendship->friend : $friendship->user;

    //             // Agregar el estado de la relación (amigo) al objeto de usuario
    //             $friend->relationship_status = 'aceptado';
    //             // Contar los amigos del amigo actual
    //             $friendCount = Friendship::where(function ($query) use ($friend) {
    //                 $query->where('user_id', $friend->id)
    //                     ->orWhere('friend_id', $friend->id);
    //             })
    //                 ->where('estado', 'aceptado')
    //                 ->count();

    //             $friend->friends_count = $friendCount;

    //             return $friend;
    //         });

    //         return response()->json([
    //             "message" => "Lista de amigos, obtenida exitosamente",
    //             "list" => $friendList,
    //         ], Response::HTTP_OK);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             "message" => "Usuario no encontrado",
    //             "error" => $e->getMessage(),
    //         ], Response::HTTP_NOT_FOUND); //404

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => "No se logro obtener la lista de amigos, intentelo nuevamente.",
    //             "error" => $e->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    // }
    public function listFriends($id)
    {
        try {
            $user = User::findOrFail($id);
            $authUserId = Auth::id(); // Obtener el ID del usuario autenticado

            $friendships = Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
                ->where('estado', 'aceptado')
                ->with(['user.userData', 'friend.userData'])
                ->get();

            $friendList = $friendships->map(function ($friendship) use ($user, $authUserId) {
                // Obtener el amigo del usuario actual
                $friend = $friendship->user_id == $user->id ? $friendship->friend : $friendship->user;

                // Determinar el estado de la relación respecto al usuario autenticado
                if ($friend->id == $authUserId) {
                    $friend->relationship_status = 'Mi perfil';
                } else {
                    $friendshipWithAuthUser = Friendship::where(function ($query) use ($authUserId, $friend) {
                        $query->where('user_id', $authUserId)
                            ->where('friend_id', $friend->id);
                    })->orWhere(function ($query) use ($authUserId, $friend) {
                        $query->where('user_id', $friend->id)
                            ->where('friend_id', $authUserId);
                    })->first();

                    if ($friendshipWithAuthUser) {
                        if ($friendshipWithAuthUser->estado == 'pendiente') {
                            if ($friendshipWithAuthUser->user_id == $authUserId) {
                                $friend->relationship_status = 'pending_sent';
                            } else {
                                $friend->relationship_status = 'pending_received';
                            }
                        } else {
                            $friend->relationship_status = $friendshipWithAuthUser->estado;
                        }
                    } else {
                        $friend->relationship_status = 'no_relation';
                    }
                }

                // Contar los amigos del amigo actual
                $friendCount = Friendship::where(function ($query) use ($friend) {
                    $query->where('user_id', $friend->id)
                        ->orWhere('friend_id', $friend->id);
                })
                    ->where('estado', 'aceptado')
                    ->count();

                $friend->friends_count = $friendCount;

                return $friend;
            });

            return response()->json([
                "message" => "Lista de amigos, obtenida exitosamente",
                "list" => $friendList,
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "error" => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND); //404
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logró obtener la lista de amigos, inténtelo nuevamente.",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getFriendsCount($id)
    {
        try {
            $user = User::findOrFail($id);

            $friendsCount = Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
                ->where('estado', 'aceptado')
                ->count();

            return response()->json([
                'friendsCount' => $friendsCount,
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la cantidad de amigos.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
