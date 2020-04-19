<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::apiResource('/padron','Padron\PadronController');
//Route::get('padron/lista', 'Padron\PadronController@getPadron'); 
//Route::apiResource('/padron/lista','Padron\PadronController@getPadron');
Route::name('user-info')->get('generar/password', 'Padron\PadronController@actualizarPassword');  
Route::name('psicologo')->get('liquidacion/by/psicologo', 'Padron\PadronController@getLiquidacionByPsicologo'); 
Route::name('psicologo')->get('padron/obra_social', 'Padron\PadronController@getObraSocial'); 
Route::name('psicologo')->get('padron', 'Padron\PadronController@getPadron'); 
Route::name('psicologo')->get('padron/correo', 'Padron\PadronController@actualizarCorreo'); 
Route::name('psicologo')->get('token/generar', 'Padron\PadronController@generarTokenValidacion'); 


Route::name('psicologo')->post('liquidacion/detalle', 'Padron\PadronController@getLiquidacionDetalleByPsicologo'); 
Route::name('psicologo')->post('liquidacion/detalle/obrasocial', 'Padron\PadronController@getLiquidacionDetalleObraSocialPagoByPsicologo');   

Route::post('oauth/token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

//Auth::routes(['register' => false]);


Route::name('psicologo')->get('informacion/publica', 'Padron\LandingController@getinformacionPublico'); 
Route::name('psicologo')->get('informacion/privada', 'Padron\LandingController@getinformacionPrivado'); 
Route::name('psicologo')->get('informacion/privada/facturacion/by/matricula', 'Padron\PadronController@getFacturaByMatricula'); 
Route::name('psicologo')->get('informacion/privada/facturacion/by/idliquidacion', 'Padron\PadronController@getFacturaByLiquidacion'); 
Route::name('psicologo')->get('liquidacion/liquidacion/generada', 'Padron\PadronController@getLiquidacionGenerada'); 


/** FILE MANAGER **/
Route::name('archivos')->post('/multiuploads/estudios/{mat_matricula}/{id_liquidacion_detalle}/{id_liquidacion}', 'Upload\UploadController@showUploadFile');
Route::name('archivos')->post('/multiuploads/estudios/datos', 'Upload\UploadController@showUploadFileDatos');
Route::name('archivos')->post('/multiuploads/texto', 'Files\FilesController@createTestTextFile'); 
Route::name('archivos')->post('/multiuploads/texto/cirugia', 'Files\FilesController@createTestTextFileCirugia'); 
Route::name('archivos')->get('/multiuploads/estudios/verimagen', 'Upload\UploadController@getEstudioImagenes'); 












Route::name('user-info')->get('user/password', 'User\UserController@getPassword'); 
Route::name('user-info')->get('user/info/menu', 'User\UserController@getUserDataAndMenu'); 
Route::name('user-info')->get('user/menu', 'User\UserController@getMenu');
Route::name('user-info')->post('user/menu/add/{id}', 'User\UserController@agregarMenuUsuario');
Route::name('user-info')->delete('user/menu/{id}', 'User\UserController@borrarMenuUsuario');
Route::resource('user', 'User\UserController');

/********************* */





/** FILE MANAGER **/
Route::name('archivos')->post('/multiuploads/estudios', 'Upload\UploadController@showUploadFile');
Route::name('archivos')->post('/multiuploads/estudios/datos', 'Upload\UploadController@showUploadFileDatos');
Route::name('archivos')->post('/multiuploads/texto', 'Files\FilesController@createTestTextFile'); 
Route::name('archivos')->get('/multiuploads/estudios/verimagen', 'Upload\UploadController@getEstudioImagenes'); 

