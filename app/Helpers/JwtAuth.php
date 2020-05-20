<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'esto_es_una_clave_super_secreta-99887766';
    }

    public function signup($email, $password, $getToken = null){
    //Buscar si existe el usuario con sus credenciales
    $user = User::where([
        'email' => $email,
        'password' => $password
    ])->first();
    //comprobar si son correctas(Objeto)
    $signup = false;
    if(is_object($user)){
        $signup = true;
    }
    //Generar el token con los datos del usuario indentificado
    if($signup){
        $token = array(
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'surname' => $user->surname,
            'iat' => time(),
            'exp' => time() + (7*24*60*60)
        );

        $jwt = JWT::encode($token,$this->key, 'HS256');
        $decode = JWT::decode($jwt, $this->key,['HS256']);
        //devolver los datos decodificados o el toke en funcion de un parametro
        if(is_null($getToken)){
            $data = $jwt;
        }else{
            $data = $decode;
        }



    }else{
        $data = array(
            'status' => 'errors',
            'message' => 'login incorrecto'
        );
    }


    return $data;

}

}

