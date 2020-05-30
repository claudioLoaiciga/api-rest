<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth',['except' => ['index','show','getImage','getPostsByCategory','getPostsByUser']]);
    }

    public function index(){
        $post = Post::all()->load('category');
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'post' => $post
        ], 200);
    }

    public function show($id){
        $post = Post::find($id)->load('category');

        if(is_object($post)){
        $data = [
            'code' => 200,
            'status' => 'success',
            'post' => $post
        ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'entrada no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //recoger datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
       
        
        if(!empty($params_array)){
            //conseguir el usuario indentificado
            $user = $this->getIdentity($request);

            //validar datos
            $validate = \Validator::make($params_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
              ]);

            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado El post, datos incorrectos.'
                ];
            }else{
                //Guardar el articulo
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }

        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha guardado El post.'
            ];
        }

        //devolver los datos
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        //datos para devolver un error
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'datos enviados incorrectamente.'
        );

        if(!empty($params_array)){
            
        //validar los datos
        $validate = \Validator::make($params_array,[
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
          ]);
        if($validate->fails()){
            $data['errors'] = $validate->errors();
            return response()->json($data, $data['code']);
        }
        //excluir lo que no queremos actualizar
        unset($params_array['id']);
        unset($params_array['user_id']);
        unset($params_array['created_at']);
        unset($params_array['user']);
        //conseguir el usuario indentificado
        $user = $this->getIdentity($request);
        
        //buscar el registro
        $post = Post::where('id', $id)
        ->where('user_id', $user->sub)
        ->first();

        if(!empty($post) && is_object($post)){
            //actulizar el registro en concreto

            $post->update($params_array);
            //devolver los datos actualizados
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post,
                'changes' => $params_array
            );
        }  
    }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){
         //Conseguir el usuario identificado
         $user = $this->getIdentity($request);
        //Capturar el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
        if(!empty($post)){
        //Borrarlo
        $post->delete();
        //devolver algo
        
        $data = [
            'code' => 200,
            'status' => 'success',
            'post' => $post
        ];
    }else{
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => 'no existe este post'
        ];
    }
        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request){
    //Conseguir el usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
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
          \Storage::disk('images')->put($image_name, \File::get($image));

          $data = array(
              'code' => 200,
              'status' => 'success',
              'image' => $image_name,
          );
      }
     //devolver la respuesta
      return response()->json($data, $data['code']);

    }


    public function getImage($filename){
        //comprobar si existe la imagen
        $isset = \Storage::disk('images')->exists($filename);
        if($isset){
            //conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            //devolver la imagen 
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

    public function getPostsByCategory($id){
        $posts = Post::where('category_id',$id)->get();
         
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();
         
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }


}
