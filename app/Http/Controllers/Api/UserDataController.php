<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserDataController extends Controller
{
    public function allData (){
        try{

            $usersData = UserData::all();
            return response()->json([
                "message" => "Datos de Usuarios Obtenidos.",
                "data" => $usersData
            ], Response::HTTP_OK); //200

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se pudieron obtener los datos",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);  //400
        }
    }
    public function register(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required',
                'apellido' => 'required',
                'fecha_nac' => 'required',
                'sexo' => 'required',
                'email' => 'required|unique:user_data',
                'telefono' => 'required|unique:user_data',
                'ruta_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'user_id' => 'required|exists:users,id',
            ],[
                'email.unique' => 'El correo electronico ya esta registrado con otro usuario.',
                'telefono.unique' => 'El telefono ya esta registrado con otro usuario.'
            ]);

            $image = $request->file('ruta_foto');
            if ($image) {
                $imageName = 'img_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('img_user', $imageName, 'public');
            } else {
                $imagePath = null;
            }


            $userData = new UserData();
            $userData->nombre = $request->nombre;
            $userData->apellido = $request->apellido;
            $userData->fecha_nac = $request->fecha_nac;
            $userData->sexo = $request->sexo;
            $userData->email = $request->email;
            $userData->telefono = $request->telefono;
            $userData->ruta_foto = $imagePath;
            $userData->user_id = $request->user_id;
            $userData->save();

            return response()->json([
                "message" => "Datos del Usuario registrados exitosamente.",
                "userData" => $userData
            ], Response::HTTP_CREATED); //201
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "message" => "No se logro registrar los Datos del Usuario.",
                "errors" => $e->errors()
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function delete($id){
        try {
            $dataUser = UserData::find($id);

            if (!$dataUser) {
                return response()->json([
                    "message" => "Datos de Usuario no Encontrados."
                ], Response::HTTP_NOT_FOUND); //404
            }

            if ($dataUser->ruta_foto) {
                Storage::disk('public')->delete($dataUser->ruta_foto);
            }

            $dataUser->delete();
            return response()->json([
                "message" => "Datos de Usuario Eliminado, exitosamente."
            ], Response::HTTP_OK); //200

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se pudo eliminar.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }

    public function update(Request $request, $id){
        try {
            $userData = UserData::findOrFail($id);

            $request->validate([
                'nombre' => 'required',
                'apellido' => 'required',
                'fecha_nac' => 'required',
                'sexo' => 'required',
                'email' => 'required|email',
                'telefono' => 'required',
                'ruta_foto' => 'nullable|image|mimes:jpeg,png,jgp,gif,svg|max:2048',
                // 'user_id' => 'required|exists:users,id',
            ]);

            $userData->nombre = $request->nombre;
            $userData->apellido = $request->apellido;
            $userData->fecha_nac = $request->fecha_nac;
            $userData->sexo = $request->sexo;
            $userData->email = $request->email;
            $userData->telefono = $request->telefono;

            if ($request->hasFile('ruta_foto')) {
                if ($userData->ruta_foto) {
                    Storage::disk('public')->delete($userData->ruta_foto);
                }

                $image = $request->file('ruta_foto');
                $imageName = 'img_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('img_user', $imageName, 'public');
                $userData->ruta_foto = $imagePath;
            }
            
            $userData->save();

            return response()->json([
                "message" => "Datos del Usuario actualizados exitosamente.",
                "data" => $userData
            ], Response::HTTP_OK); //200

        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro actualizar los datos del Usuario.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST); //400
        }
    }
}
