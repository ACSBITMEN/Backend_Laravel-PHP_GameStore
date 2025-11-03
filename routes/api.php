<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/** 
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

/** MOSTRAR TODOS LOS USUARIOS */
Route::get('/users', function () {
    return 'MOSTRAR TODOS LOS USUARIOS // Esto es un mensaje de prueba';
});
/** MOSTRAR (1) USUARIO */
Route::get('/users/{id}', function () {
    return 'MOSTRAR (1) USUARIO ESPECIFICO // Esto es un mensaje de prueba USUARIOS';
});
/** CREAR (1) USUARIO */
Route::post('/users', function () {
    return 'CREACION DE (1) USUARIO // Esto es un mensaje de prueba USUARIOS';
});
/** EDITAR (1) USUARIO - TODOS LOS CAMPOS*/
Route::put('/users/{id}', function () {
    return 'ACTUALIZAR (1) USUARIO ESPECIFICO // Esto es un mensaje de prueba USUARIOS';
});
/** ELIMINAR (1) USUARIO */
Route::delete('/users/{id}', function () {
    return 'ELIMINAR (1) USUARIO ESPECIFICO // Esto es un mensaje de prueba USUARIOS';
});
