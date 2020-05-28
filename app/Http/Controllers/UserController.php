<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas de UserController";
    }

    public function register(Request $request){

        //Recoger los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        if(!empty($params) && !empty($params_array)){

        //limpiar datos
        $params_array = array_map('trim',$params_array);
        //validar los datos del usuario
        $validate = \Validator::make($params_array,[
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            //comprobar si ya exite el usuario
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if($validate->fails()){


            $data = array(
                'status' => 'errors',
                'code' => 404,
                'message' => 'El usario no se ha creado',
                'errors' => $validate->errors()
            );
        }else{


        //cifrar las contraseña
        $pwd = hash('sha256',$params->password);
        //crear el usuario
            $user = new User();
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $pwd;
            $user->role = 'ROLE_USER';
            //Guardar el usario este metod hace un insert into a la tabla usuario de la base de datos
            $user->save();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usario se ha creado',
                'user' => $user
            );
        }

    }else{
        $data = array(
            'status' => 'errors',
            'code' => 404,
            'message' => 'los datos enviados no son correctos'
        );
    }


      return response()->json($data, $data['code']);
    }

    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        //Recibir datos por post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json,true);
        // validar eso datos
        $validate = \Validator::make($params_array,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails()){


            $signup = array(
                'status' => 'errors',
                'code' => 404,
                'message' => 'El usario no puede iniciar secion',
                'errors' => $validate->errors()
            );
        }else{
            //cifrar la contraseña
            $pwd = hash('sha256',$params->password);
            //Devolver el toke o datos
            $signup = $jwtAuth->signup($params->email, $pwd);
            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
        return response()->json($signup,200);
    }

    public function update(Request $request){
        //Comprobar si el usuario esta indentificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        //Recoger los datos por post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array) ){
            //Actualizar el usuario

            //sacar el usuario identificado
            $user = $jwtAuth->checkToken($token,true);
            //validar los datos
            $validate = \Validator::make($params_array,[
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                //comprobar si ya exite el usuario
                'email' => 'required|email|unique:users, '.$user->sub
            ]);

            //quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //actualizar usuario en la bbdd
            $user_update = User::where('id', $user->sub)->update($params_array);


            //devolver array con su  resultado\
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usario se ha actulizado',
                'user' => $user,
                'changes' => $params_array
            );

        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usario no esta indentificado'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){
        //Recoger datos de la peticion
        $image = $request->file('file0');

        //validacion de la imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar imagen
        if(!$image || $validate->fails()){
              $data = array(
              'code' => 400,
              'status' => 'error',
               'message' => 'Error al subir imagen.'
            );
          
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            );
        }
       
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if($isset){
            $file = \Storage::disk('users')->get($filename); 
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No exite la imagen'
            );

            return response()->json($data, $data['code']);
        }
   
    }

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'users' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe este usuario'
            );
        }

        return response()->json($data, $data['code']);
    }
}
