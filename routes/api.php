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


Route::name('user-info')->get('user/info/menu', 'User\UserController@getUserDataAndMenu');
Route::name('user-info')->get('user/menu', 'User\UserController@getMenu');

Route::name('user-info')->delete('user/menu/{id}', 'User\UserController@borrarMenuUsuario');
Route::name('user-info')->get('user/listado', 'User\UserController@getUsuarios');

Route::resource('user', 'User\UserController');

Route::group(['middleware' => 'admin'], function () {
Route::name('user-info')->get('user/password', 'User\UserController@getPassword');
Route::name('user-info')->post('user/crear', 'User\UserController@CrearUsuario');
Route::name('user-info')->put('user/editar/{id}', 'User\UserController@EditarUsuario');
Route::name('user-info')->put('user/editar/password/{id}', 'User\UserController@EditarUsuarioPassword');
Route::name('user-info')->post('user/menu/add/{id}', 'User\UserController@agregarMenuUsuario');
});



Route::post('oauth/token','\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

/* -------------------------------------------------------------------------- */
/*                                  MATRICULA                                 */
/* -------------------------------------------------------------------------- */

Route::group(['middleware' => 'admin'], function () {
    // PROTEJO LAS RUTAS PARA SABER SI ESTA HABILITADO EL USUARIO

Route::name('psicologo')->get('matricula', 'Matricula\MatriculaController@getMatricula');
Route::name('psicologo')->get('matriculas', 'Matricula\MatriculaController@getMatriculas');
Route::name('psicologo')->get('matricula/obra/social', 'Matricula\MatriculaController@getMatriculaObraSocial');
Route::name('psicologo')->get('padron/obra/social', 'Matricula\MatriculaController@getPadronObraSocial');
Route::name('psicologo')->put('matricula/{id}',  'Matricula\MatriculaController@putMatricula');
Route::name('psicologo')->post('matricula', 'Matricula\MatriculaController@setMatricula');
Route::name('psicologo')->delete('matricula/{id}', 'Matricula\MatriculaController@borrarMatriculaObraSocial');
Route::name('psicologo')->post('matricula/obra/social/add/{id}', 'Matricula\MatriculaController@setMatriculaObraSocial');

Route::name('paciente')->get('paciente/todos/limite', 'Matricula\MatriculaController@getPacientes');
Route::name('paciente')->get('paciente/by/condicion', 'Matricula\MatriculaController@getPacienteByCondicion');
});
Route::name('paciente')->post('paciente/nuevo', 'Matricula\MatriculaController@setPaciente');
Route::name('paciente')->put('paciente/{id}',  'Matricula\MatriculaController@putPaciente');


/* -------------------------------------------------------------------------- */
/*                                 OBRA SOCIAL                                */
/* -------------------------------------------------------------------------- */

Route::group(['middleware' => 'admin'], function () {
Route::name('obra-social')->get('obra/social', 'ObraSocial\ObraSocialController@getObraSocial');
Route::name('obra-social')->get('obra/social/habilitada', 'ObraSocial\ObraSocialController@getObraSocialHabilitado');
Route::name('obra-social')->put('obra/social/{id}',  'ObraSocial\ObraSocialController@putObraSocial');
Route::name('obra-social')->post('obra/social', 'ObraSocial\ObraSocialController@setObraSocial');
Route::name('obra-social')->get('obra/social/convenio', 'ObraSocial\ObraSocialController@getConvenioByObraSocial');
Route::name('obra-social')->get('obra/social/convenio/habilitada', 'ObraSocial\ObraSocialController@getConvenioByObraSocialHabilitado');
Route::name('obra-social')->get('convenio/habilitada', 'ObraSocial\ObraSocialController@getConvenioHabilitado');
Route::name('convenio')->put('convenio/{id}',  'ObraSocial\ObraSocialController@putConvenio');
Route::name('convenio')->post('convenio', 'ObraSocial\ObraSocialController@setConvenio');
});



/* -------------------------------------------------------------------------- */
/*                                   COBROS                                   */
/* -------------------------------------------------------------------------- */


Route::group(['middleware' => 'admin'], function () {


Route::name('concepto')->get('concepto', 'Cobro\CobroController@getConcepto');
Route::name('concepto')->post('concepto',  'Cobro\CobroController@setConcepto');
Route::name('concepto')->put('concepto/{id}',  'Cobro\CobroController@putConcepto');

Route::name('cobros')->get('cobro/by/matricula', 'Cobro\CobroController@getDeudaByMatricula');
Route::name('cobros')->get('cobro/by/matricula/estado', 'Cobro\CobroController@getDeudaByMatriculaAndEstado');
Route::name('cobros')->get('cobro/registro/eliminar', 'Cobro\CobroController@putRegistroCobroEliminado');


Route::name('cobros')->get('cobro/by/matricula/plan', 'Cobro\CobroController@getDeudaByPlanAndMatricula');
Route::name('cobros')->get('cobro/by/matricula/by/dates', 'Cobro\CobroController@getDeudaBydMatriculaBetweenDates');
Route::name('cobros')->get('cobro/plan', 'Cobro\CobroController@getPlanes');
Route::name('cobros')->put('cobro/by/matricula/actualizar/{id}',  'Cobro\CobroController@putDeuda');
Route::name('cobros')->post('cobro/by/matricula', 'Cobro\CobroController@setDeuda');
Route::name('cobros')->post('cobro/by/matricula/registros/nuevos', 'Cobro\CobroController@setDeudaRegistros');
Route::name('cobros')->put('cobro/by/matricula/cobrar/{id}',  'Cobro\CobroController@putRegistroCobro');

Route::name('plan')->get('plan/ultimo', 'Cobro\CobroController@getUltimoPlanPago');
Route::name('plan')->post('plan/by/matricula', 'Cobro\CobroController@setPlanPagoMatricula');

//Route::name('deuda')->get('deuda/psicologo/todos', 'Cobro\CobroController@generarDeudaPsicologos');
Route::name('deuda')->get('deuda/psicologo', 'Cobro\CobroController@generarDeudaPsicologo');
Route::name('cobros')->get('informe/deuda/psicologo', 'Cobro\CobroController@getPadronDeudaByDate');

});

Route::name('cobros')->get('cobro/by/matricula/estado/detalle/liquidacion', 'Cobro\CobroController@getDeudaByMatriculaAndEstadoByIdLiquidacionDetalle');

/* -------------------------------------------------------------------------- */
/*                                 LIQUIDACION                                */
/* -------------------------------------------------------------------------- */

Route::name('liquidacion')->get('liquidacion/orden/by/dates/estado/psicologo', 'Liquidacion\LiquidacionController@getLiquidacionOrdenBetweenDatesByPsicologo');
Route::group(['middleware' => 'admin'], function () {

Route::name('liquidacion')->get('liquidacion/calcular/bruto', 'Liquidacion\LiquidacionController@calcularBruto');
Route::name('liquidacion')->get('liquidacion/orden/by/estado/matricula', 'Liquidacion\LiquidacionController@getLiquidacionByMatriculaAndEstado');
Route::name('liquidacion')->get('liquidacion/orden/by/dates/estado', 'Liquidacion\LiquidacionController@getLiquidacionOrdenBetweenDates');
Route::name('liquidacion')->post('liquidacion/orden',  'Liquidacion\LiquidacionController@setOrden');
Route::name('liquidacion')->put('liquidacion/orden/{id}',  'Liquidacion\LiquidacionController@putOrden');
Route::name('liquidacion')->post('liquidacion/orden/auditar',  'Liquidacion\LiquidacionController@auditarOrdenes');
Route::name('liquidacion')->get('liquidacion/generada', 'Liquidacion\LiquidacionController@getLiquidaciones');
Route::name('liquidacion')->get('liquidacion/ultimonumero/ingbrutos', 'Liquidacion\LiquidacionController@getUltimoNroIngBrutos');
Route::name('liquidacion')->get('liquidacion/ultimonumero/recibo', 'Liquidacion\LiquidacionController@getUltimoNroRecibo');
Route::name('expediente')->post('liquidacion/actualizar/ingbrutos',  'Liquidacion\LiquidacionController@putActualizarNroIngBrutos');
Route::name('expediente')->post('liquidacion/actualizar/recibo',  'Liquidacion\LiquidacionController@putActualizarNroRecibo');
Route::name('expediente')->post('liquidacion/expediente/liquidacion/id/seleccionado',  'Liquidacion\LiquidacionController@obtenerLiquidacionDetalleSeleccionadas');
Route::name('expediente')->get('liquidacion/expediente/estado',  'Liquidacion\LiquidacionController@getExpedienteByEstado');
Route::name('expediente')->post('liquidacion/expediente/liquidacion/generar',  'Liquidacion\LiquidacionController@generarLiquidacion');
Route::name('ingresobruto')->get('liquidacion/ingreso/bruto/ultimo',  'Liquidacion\LiquidacionController@getUltimoIngresoBruto');

});

Route::name('liquidacion')->get('liquidacion/obrasocial/detalle', 'Liquidacion\LiquidacionController@getObrasSocialesByLiquidacion');
Route::name('liquidacion')->get('liquidacion/detalle/by/matricula', 'Liquidacion\LiquidacionController@getLiquidacionDetalleByMatricula');
Route::name('liquidacion')->get('liquidacion/detalle/by/id/liquidacion', 'Liquidacion\LiquidacionController@getLiquidacionDetalleByidLiquidacion');
Route::name('expediente')->get('liquidacion/expediente/liquidacion/id',  'Liquidacion\LiquidacionController@getExpedienteByIdLiquidacion');
/* -------------------------------------------------------------------------- */
/*                                 LIQUIDACION                                */
/* -------------------------------------------------------------------------- */

Route::group(['middleware' => 'admin'], function () {
Route::name('liquidacion')->post('liquidacion/expediente/afectar',  'Liquidacion\LiquidacionController@afectarOrdenes');
Route::name('liquidacion')->put( 'liquidacion/expediente/actualizar/{id}',  'Liquidacion\LiquidacionController@putExpediente');
Route::name('liquidacion')->get( 'liquidacion/expediente/desafectar',  'Liquidacion\LiquidacionController@desafectarExpediente');
Route::name('liquidacion')->get( 'liquidacion/liquidar',  'Liquidacion\LiquidacionController@liquidar');

Route::name('liquidacion')->put( 'liquidacion/registro/detalle/{id}',  'Liquidacion\LiquidacionController@putLiquidacionDetalle');
Route::name('liquidacion')->put( 'liquidacion/orden',  'Liquidacion\LiquidacionController@auditarOrdenes');
Route::name('liquidacion')->put( 'liquidacion/generar/expediente',  'Liquidacion\LiquidacionController@putGenerarExpediente');
Route::name('liquidacion')->post( 'liquidacion/generar/expediente',  'Liquidacion\LiquidacionController@setGenerarExpediente');
Route::name('liquidacion')->post( 'liquidacion/generar/liquidacion/detalle',  'Liquidacion\LiquidacionController@generarLiquidacionDetalle');
Route::name('orden')->delete('orden/eliminar/{id}',  'Liquidacion\LiquidacionController@destroyOrdenById');
Route::name('liquidacion')->get( 'liquidacion/actuacion/profesional',  'Liquidacion\LiquidacionController@getActuacionProfesionalByMatricula');

});

Route::name('liquidacion')->get( 'liquidacion/orden/by/matricula/liquidacion',  'Liquidacion\LiquidacionController@getOrdenByMatriculaAndLiquidacion');







/* -------------------------------------------------------------------------- */
/*                             MOVIMIENTOS DE CAJA                            */
/* -------------------------------------------------------------------------- */
Route::group(['middleware' => 'admin'], function () {

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




//Auth::routes(['register' => false]);


Route::name('psicologo')->get('informacion/publica', 'Padron\LandingController@getinformacionPublico');
Route::name('psicologo')->get('informacion/privada', 'Padron\LandingController@getinformacionPrivado');
Route::name('psicologo')->get('informacion/privada/facturacion/by/matricula', 'Padron\PadronController@getFacturaByMatricula');
Route::name('psicologo')->get('informacion/privada/facturacion/by/idliquidacion', 'Padron\PadronController@getFacturaByLiquidacion');
Route::name('psicologo')->get('liquidacion/liquidacion/generada', 'Padron\PadronController@getLiquidacionGenerada');
Route::name('psicologo')->get('liquidacion/liquidacion/generada', 'Padron\PadronController@getLiquidacionGenerada');



/** FILE MANAGER **/
Route::name('archivos')->post('/multiuploads/estudios/{mat_matricula}/{id_liquidacion_detalle}/{id_liquidacion}', 'Upload\UploadController@showUploadFile');
Route::name('archivos')->post('/multiuploads/estudios/datos', 'Upload\UploadController@showUploadFileDatos');
Route::name('archivos')->post('/multiuploads/texto', 'Files\FilesController@createTestTextFile');
Route::name('archivos')->post('/multiuploads/texto/cirugia', 'Files\FilesController@createTestTextFileCirugia');
Route::name('archivos')->get('/multiuploads/estudios/verimagen', 'Upload\UploadController@getEstudioImagenes');
Route::name('archivos')->get('/files/rentas/by/liquidacion', 'Files\FilesController@createTextFileRentas');
Route::name('archivos')->post('files/dos/by/excel', 'Files\FilesController@createTextFileDos');



/* -------------------------------------------------------------------------- */
/*                                  NOTICIAS                                  */
/* -------------------------------------------------------------------------- */

Route::name('archivos')->post('/noticia/publica', 'Padron\LandingController@setNoticiaPublico');
Route::name('archivos')->put('/noticia/publica/{id}',  'Padron\LandingController@putNoticiaPublico');

Route::name('archivos')->post('/noticia/privada', 'Padron\LandingController@setNoticiaPrivado');
Route::name('archivos')->put('/noticia/privada/{id}',  'Padron\LandingController@putNoticiaPrivado');



/* -------------------------------------------------------------------------- */
/*                             FACTURA ELECTRONICA                            */
/* -------------------------------------------------------------------------- */


Route::name('factura-data')->get('afip/data/medicos/facturan', 'Afip\AfipController@getMedicosFacturan');

Route::name('factura-data')->get('afip/data/getlastvoucher', 'Afip\AfipDatosController@GetLastVoucher');
Route::name('factura-data')->get('afip/data/getiformacioncomprobante', 'Afip\AfipDatosController@getIformacionComprobante');
Route::name('factura-data')->get('afip/data/tipocomprobantesdisponibles', 'Afip\AfipDatosController@TipoComprobantesDisponibles');
Route::name('factura-data')->get('afip/data/tipoconceptosdisponibles', 'Afip\AfipDatosController@GetConceptTypes');
Route::name('factura-data')->get('afip/data/tipodocumentosdisponibles', 'Afip\AfipDatosController@TipoDocumentosDisponibles');
Route::name('factura-data')->get('afip/data/tipoalicuotasdisponibles', 'Afip\AfipDatosController@TipoAlicuotasDisponibles');
Route::name('factura-data')->get('afip/data/getoptionstypes', 'Afip\AfipDatosController@GetOptionsTypes');
Route::name('factura-data')->get('afip/data/gettaxtypes', 'Afip\AfipDatosController@GetTaxTypes');
Route::name('factura-data')->get('afip/data/getconcepttypes', 'Afip\AfipDatosController@GetConceptTypes');
Route::name('factura-data')->get('afip/data/obetenerestadodelservidor', 'Afip\AfipDatosController@ObetenerEstadoDelServidor');
Route::name('factura-data')->get('afip/data/medico/dato', 'Afip\AfipController@getDatoMedico');

Route::name('factura')->get('afip/lastvoucher', 'Afip\AfipController@testAfipGetLastVoucher');
Route::name('factura')->get('afip/test', 'Afip\AfipController@testAfip');
Route::name('factura')->get('afip/factura/a', 'Afip\AfipController@CrearFacturaA');
Route::name('factura')->get('afip/factura/b', 'Afip\AfipController@CrearFacturaB');
Route::name('factura')->get('afip/factura/c', 'Afip\AfipController@CrearFacturaC');

Route::name('factura')->get('afip/notacredito/a', 'Afip\AfipController@CrearNotaCreditoA');
Route::name('factura')->get('afip/notacredito/b', 'Afip\AfipController@CrearNotaCreditoB');
Route::name('factura')->get('afip/notacredito/c', 'Afip\AfipController@CrearNotaCreditoC');

Route::name('factura')->get('afip/factura/info', 'Afip\AfipController@GetVoucherInfo');


/* -------------------------------------------------------------------------- */
/*                      ELEMENTOS DE FACTURA ELECTRONICA                      */
/* -------------------------------------------------------------------------- */

Route::name('facturacion-elementos')->get('afip/elementos/alicuota', 'Afip\FacturaElementosController@Alicuota');
Route::name('facturacion-elementos')->get('afip/elementos/alicuota/asociada', 'Afip\FacturaElementosController@AlicuotaAsociada');
Route::name('facturacion-elementos')->get('afip/elementos/comprobante', 'Afip\FacturaElementosController@Comprobante');
Route::name('facturacion-elementos')->get('afip/elementos/concepto', 'Afip\FacturaElementosController@Concepto');
Route::name('facturacion-elementos')->get('afip/elementos/documento', 'Afip\FacturaElementosController@Documento');
Route::name('facturacion-elementos')->get('afip/elementos/pto/vta', 'Afip\FacturaElementosController@PtoVta');
Route::name('facturacion-elementos')->get('afip/elementos/categoria/iva', 'Afip\FacturaElementosController@CategoriaIva');
Route::name('facturacion-elementos')->post('afip/elementos/factura/nueva', 'Afip\FacturaElementosController@crearFactura');
Route::name('facturacion-elementos')->get('afip/elementos/factura', 'Afip\FacturaElementosController@GetFacturaByid');
Route::name('facturacion-elementos')->post('afip/elementos/factura/nota/credito', 'Afip\FacturaElementosController@crearFacturaNotaCredito');
Route::name('facturacion-elementos')->get('afip/elementos/factura/by/fecha', 'Afip\FacturaElementosController@GetFacturaBetweenDates');
Route::name('facturacion-elementos')->get('afip/elementos/factura/by/cliente', 'Afip\FacturaElementosController@GetFacturaByNameOrDocumento');
Route::name('facturacion-elementos')->get('afip/elementos/factura/by/id', 'Afip\FacturaElementosController@FacturaById');
Route::name('facturacion-elementos')->get('afip/elementos/factura/libro/iva', 'Afip\FacturaElementosController@getLibroIva');

Route::name('facturacion-elementos')->get('afip/elementos/articulo', 'Afip\FacturaElementosController@FacturaArticulo');
Route::name('facturacion-elementos')->post('afip/elementos/articulo', 'Afip\FacturaElementosController@CrearFacturaArticulo');
Route::name('facturacion-elementos')->put('afip/elementos/articulo/{id}', 'Afip\FacturaElementosController@ActualizarFacturaArticulo');
Route::name('facturacion-elementos')->get('afip/elementos/articulo/tipo', 'Afip\FacturaElementosController@GetFacturaByArticuloTipo');
Route::name('facturacion-elementos')->put('afip/elementos/articulo/tipo/{id}', 'Afip\FacturaElementosController@ActualizarFacturaArticuloTipo');
Route::name('facturacion-elementos')->post('afip/elementos/articulo/tipo', 'Afip\FacturaElementosController@CrearFacturaArticuloTipo');










Route::name('user-info')->get('user/password', 'User\UserController@getPassword');

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

