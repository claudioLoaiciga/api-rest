<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/**
 * METODOS HTTP
 * GET: Capturar datos
 * POST: Guardar datos
 * PUT: Actualizar recursos
 * DELETE: Eliminar recursos
 */

//Rutas de prueba
Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre?}', function ($nombre = null) {

    $texto='<h2>texto desde una ruta</h2>';
    $texto.='Nombre: '.$nombre;

    return view('pruebas', array(
        'texto' => $texto
    )) ;
});

Route::get('/animales','pruebasController@index');

Route::get('/test-orm', 'pruebasController@testOrm');

//RUTAS DEL API

    //rutas de pruebas
    Route::get('/usuario/pruebas', 'UserController@pruebas');
    Route::get('/categoria/pruebas', 'CategoryController@pruebas');
    Route::get('/post/pruebas', 'PostController@pruebas');

    //rutas del controlador de usuario
    Route::post('/api/register', 'UserController@register');
    Route::post('/api/login', 'UserController@login');
    Route::post('/api/user/update', 'UserController@update');

