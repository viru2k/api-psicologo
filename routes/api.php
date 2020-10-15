<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|Modulo movimiento de caja - Desarrollo y migración de datos cuota 1/2	
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('user-info')->get('user/password', 'User\UserController@getPassword'); 
Route::name('user-info')->get('user/info/menu', 'User\UserController@getUserDataAndMenu'); 
Route::name('user-info')->get('user/menu', 'User\UserController@getMenu');
Route::name('user-info')->post('user/menu/add/{id}', 'User\UserController@agregarMenuUsuario');
Route::name('user-info')->delete('user/menu/{id}', 'User\UserController@borrarMenuUsuario');
Route::name('user-info')->get('user/listado', 'User\UserController@getUsuarios');
Route::name('user-info')->post('user/crear', 'User\UserController@CrearUsuario');
Route::name('user-info')->put('user/editar/{id}', 'User\UserController@EditarUsuario');
Route::name('user-info')->put('user/editar/password/{id}', 'User\UserController@EditarUsuarioPassword');
Route::resource('user', 'User\UserController');





Route::post('oauth/token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

/* -------------------------------------------------------------------------- */
/*                                  MATRICULA                                 */
/* -------------------------------------------------------------------------- */

Route::name('psicologo')->get('matricula', 'Matricula\MatriculaController@getMatricula'); 
Route::name('psicologo')->get('matriculas', 'Matricula\MatriculaController@getMatriculas'); 
Route::name('psicologo')->get('matricula/obra/social', 'Matricula\MatriculaController@getMatriculaObraSocial'); 
Route::name('psicologo')->put('matricula/{id}',  'Matricula\MatriculaController@putMatricula'); 
Route::name('psicologo')->post('matricula', 'Matricula\MatriculaController@setMatricula'); 
Route::name('psicologo')->delete('matricula/{id}', 'Matricula\MatriculaController@borrarMatriculaObraSocial');
Route::name('psicologo')->post('matricula/obra/social/add/{id}', 'Matricula\MatriculaController@setMatriculaObraSocial'); 



/* -------------------------------------------------------------------------- */
/*                                 OBRA SOCIAL                                */
/* -------------------------------------------------------------------------- */



Route::name('obra-social')->get('obra/social', 'ObraSocial\ObraSocialController@getObraSocial'); 
Route::name('obra-social')->put('obra/social/{id}',  'ObraSocial\ObraSocialController@putObraSocial'); 
Route::name('obra-social')->post('obra/social', 'ObraSocial\ObraSocialController@setObraSocial');

/* -------------------------------------------------------------------------- */
/*                                   COBROS                                   */
/* -------------------------------------------------------------------------- */

Route::name('cobros')->get('cobro/by/matricula', 'Cobro\CobroController@getDeudaByMatricula'); 
Route::name('cobros')->get('cobro/by/matricula/plan', 'Cobro\CobroController@getDeudaByPlanAndMatricula'); 
Route::name('cobros')->get('cobro/by/matricula/by/dates', 'Cobro\CobroController@getDeudaBydMatriculaBetweenDates');  
Route::name('cobros')->get('cobro/plan', 'Cobro\CobroController@getPlanes');





/* -------------------------------------------------------------------------- */
/*                             MOVIMIENTOS DE CAJA                            */
/* -------------------------------------------------------------------------- */
Route::name('movimiento-caja')->get('movimiento/concepto/moneda', 'MovimientosCaja\MovimientosCajaController@getConceptoMoneda'); 
Route::name('movimiento-caja')->get('movimiento/concepto/monedas', 'MovimientosCaja\MovimientosCajaController@getConceptoMonedas'); 
Route::name('movimiento-caja')->post('movimiento/concepto/moneda', 'MovimientosCaja\MovimientosCajaController@setConceptoMoneda'); 
Route::name('movimiento-caja')->put('movimiento/concepto/moneda/{id}', 'MovimientosCaja\MovimientosCajaController@putConceptoMoneda'); 

Route::name('movimiento-caja')->get('movimiento/concepto/comprobante', 'MovimientosCaja\MovimientosCajaController@getConceptoTipoComprobante'); 
Route::name('movimiento-caja')->get('movimiento/concepto/comprobantes', 'MovimientosCaja\MovimientosCajaController@getConceptoTipoComprobantes'); 
Route::name('movimiento-caja')->post('movimiento/concepto/comprobante', 'MovimientosCaja\MovimientosCajaController@setConceptoTipoComprobante'); 
Route::name('movimiento-caja')->put('movimiento/concepto/comprobante/{id}', 'MovimientosCaja\MovimientosCajaController@putConceptoTipoComprobante'); 

Route::name('movimiento-caja')->get('movimiento/concepto/cuenta', 'MovimientosCaja\MovimientosCajaController@getConceptoCuenta'); 
Route::name('movimiento-caja')->get('movimiento/concepto/cuentas', 'MovimientosCaja\MovimientosCajaController@getConceptoCuentas'); 
Route::name('movimiento-caja')->post('movimiento/concepto/cuenta', 'MovimientosCaja\MovimientosCajaController@setConceptoCuenta'); 
Route::name('movimiento-caja')->put('movimiento/concepto/cuenta/{id}', 'MovimientosCaja\MovimientosCajaController@putConceptoCuenta'); 

Route::name('movimiento-caja')->get('movimiento/cuenta', 'MovimientosCaja\MovimientosCajaController@getCuenta'); 
Route::name('movimiento-caja')->get('movimiento/cuentas', 'MovimientosCaja\MovimientosCajaController@getCuentas'); 
Route::name('movimiento-caja')->post('movimiento/cuenta', 'MovimientosCaja\MovimientosCajaController@setCuenta'); 
Route::name('movimiento-caja')->put('movimiento/cuenta/{id}', 'MovimientosCaja\MovimientosCajaController@putCuenta'); 

Route::name('movimiento-caja')->get('movimiento/registro/by/date', 'MovimientosCaja\MovimientosCajaController@geRegistroMovimientoBydate'); 

Route::name('movimiento-caja')->post('movimiento/caja', 'MovimientosCaja\MovimientosCajaController@setMovimientoCaja'); 
Route::name('movimiento-caja')->put('movimiento/caja/{id}', 'MovimientosCaja\MovimientosCajaController@putMovimientoCaja'); 
/* -------------------------------------------------------------------------- */
/*                                  PROVEEDOR                                 */
/* -------------------------------------------------------------------------- */

Route::name('movimiento-caja')->get('proveedor', 'MovimientosCaja\MovimientosCajaController@getProveedor'); 
Route::name('movimiento-caja')->get('proveedores', 'MovimientosCaja\MovimientosCajaController@getProveedores'); 
Route::name('movimiento-caja')->post('proveedor', 'MovimientosCaja\MovimientosCajaController@setProveedor'); 
Route::name('movimiento-caja')->put('proveedor/{id}', 'MovimientosCaja\MovimientosCajaController@putProveedor'); 




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


/* -------------------------------------------------------------------------- */
/*                                  NOTICIAS                                  */
/* -------------------------------------------------------------------------- */

Route::name('archivos')->post('/noticia/publica', 'Padron\LandingController@setNoticiaPublico'); 
Route::name('archivos')->put('/noticia/publica/{id}',  'Padron\LandingController@putNoticiaPublico'); 

Route::name('archivos')->post('/noticia/privada', 'Padron\LandingController@setNoticiaPrivado'); 
Route::name('archivos')->put('/noticia/privada/{id}',  'Padron\LandingController@putNoticiaPrivado'); 












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

