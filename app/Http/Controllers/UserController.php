<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            echo "<h1>Login correcto</h1>";
        }else{
            echo "<h1>Login incorrecto</h1>";
        }


        die();
    }
}
