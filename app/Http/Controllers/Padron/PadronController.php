<?php

namespace App\Http\Controllers\Padron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Auth\AuthenticationException; 
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Hash;

class PadronController extends ApiController
{


/*****
 * 
 * 
  DB_DATABASE=c1351312_psicolo
DB_USERNAME=c1351312_psicolo
DB_PASSWORD=gepevoGE92
 */


public function __construct()
{
    //$this->middleware('auth:api')->except('index','getPadron');
   // $this->middleware('auth:api')->except(['index', 'show','getPadron']);
   // $this->middleware('auth:api', ['except' => ['index', 'getPadron']]); 
   $this->middleware('auth:api', ['only' => ['getLiquidacionByPsicologo', 'getLiquidacionNumero','getFacturaByMatricula','getFacturaByLiquidacion','getLiquidacion']]); 

}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
   /*      $res = DB::select( DB::raw("
        SELECT id_obra_social, os_nombre, os_capitalizada, mat_obra_social, es_habilitada FROM os_obra_social WHERE  es_habilitada = 'S'
    "));
             
    return response()->json($res, 201); */
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //echo "padron";

     //   if(request()->user()->tokensCan('read-general')){
   /*     $consulta =$request->input('consulta');        
        $valor =$request->input('valor');

        $res = DB::select( DB::raw("
        SELECT mat_matricula.mat_apellido, mat_matricula.mat_nombre, os_obra_social.os_nombre , mat_matricula_psicologo
        FROM mat_matricula, matricula_obra_social, os_obra_social 
        WHERE  matricula_obra_social.mat_matricula = mat_matricula.mat_matricula_psicologo and matricula_obra_social.obra_social_id = os_obra_social.id_obra_social 
        
        
        AND   ".$consulta."  LIKE '".$valor."%'
    "));
             
    return response()->json($res, 201);*/
     }


   


public function getObraSocial()
{
    //echo "padron";

    $res = DB::select( DB::raw("
    SELECT id_obra_social, os_nombre, os_capitalizada, mat_obra_social, es_habilitada FROM os_obra_social WHERE  es_habilitada = 'S'
"));
         
return response()->json($res, 201);

}




public function getLiquidacionNumero()
{
    $res = DB::select( DB::raw("
    SELECT id_os_liquidacion, id_os_obra_social, os_liq_numero, os_fecha_desde, os_fecha_hasta, os_cant_ordenes, os_monto_total, os_estado, id_liquidacio FROM os_liq_liquidacion
"));
         
return response()->json($res, 201);

}


public function getLiquidacionByPsicologo(Request $request)
{
    $mat_matricula =$request->input('mat_matricula');  
    $res = DB::select( DB::raw("
    SELECT `id_liquidacion_detalle`, `mat_matricula`, `os_liq_bruto`, `os_ing_brutos`, `os_lote_hogar`, `os_gasto_admin`, `os_imp_cheque`, `os_descuentos`, `os_desc_matricula`, `os_desc_fondo_sol`, `os_otros_ing_eg`, `os_liq_neto`, `num_comprobante`, `os_num_ing_bruto`,os_liq_liquidacion_generada.id_liquidacion, os_liq_liquidacion_detalle.num_comprobante, os_liq_liquidacion_detalle.id_liquidacion_generada , os_liq_liquidacion_detalle.os_num_ing_bruto, CONCAT(mat_matricula.mat_apellido,' ',mat_matricula.mat_nombre) as mat_apellido, mat_matricula.mat_domicilio_partcular, mat_matricula.mat_cuit, mat_matricula.mat_ning_bto, os_liq_liquidacion_generada.os_fecha FROM `os_liq_liquidacion_detalle`, os_liq_liquidacion_generada, mat_matricula WHERE os_liq_liquidacion_detalle.id_liquidacion_generada = os_liq_liquidacion_generada.id_liquidacion_generada AND mat_matricula.mat_matricula_psicologo = os_liq_liquidacion_detalle.mat_matricula AND mat_matricula =  ".$mat_matricula."   
    ORDER BY os_liq_liquidacion_generada.id_liquidacion_generada DESC
"));
         
return response()->json($res, 201);

}


public function getLiquidacionDetalleByPsicologo(Request $request)
{
    $mat_matricula =$request->input('mat_matricula');  
    $id_liquidacion_generada =$request->input('id_liquidacion_generada');  
    //echo $id_liquidacion_generada;
    //echo  $mat_matricula;
    $res = DB::select( DB::raw("
    SELECT os_liq_orden.mat_matricula , os_liq_orden.os_cantidad,os_liq_orden.os_precio_sesion, os_liq_orden.os_precio_total,os_obra_social.os_nombre, os_sesion_tipo.os_sesion, os_liq_liquidacion.os_fecha_hasta  FROM os_liq_orden, os_liq_liquidacion, os_obra_social, os_sesion, os_sesion_tipo, os_liq_liquidacion_generada  WHERE  os_liq_orden.os_liq_numero =  os_liq_liquidacion.id_os_liquidacion AND  os_obra_social.id_obra_social = os_liq_orden.id_obra_social   AND os_liq_orden.id_sesion = os_sesion.id_sesion  AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo AND os_liq_liquidacion.id_liquidacion = os_liq_liquidacion_generada.id_liquidacion_generada  AND  os_liq_orden.mat_matricula =  ".$mat_matricula." AND os_liq_liquidacion_generada.id_liquidacion_generada = ".$id_liquidacion_generada."
"));
         
return response()->json($res, 201);

}


public function actualizarCorreo(Request $request)
{
    $mat_matricula =$request->input('mat_matricula');  
    $mat_email =$request->input('mat_email');  
    $id = DB::table('mat_matricula') 
    ->where('mat_matricula_psicologo', $mat_matricula) ->limit(1) 
    ->update( [     
     'mat_email' => $mat_email
    	]); 
         
return response()->json($id, 201);

}



public function getLiquidacionDetalleObraSocialPagoByPsicologo(Request $request)
{
    $id_liquidacion_generada =$request->input('id_liquidacion_generada');  
    $res = DB::select( DB::raw("
    SELECT os_obra_social.os_nombre, os_fecha_hasta, os_liq_numero FROM  os_liq_liquidacion, os_obra_social, os_liq_liquidacion_generada  WHERE os_liq_liquidacion.id_os_obra_social = os_obra_social.id_obra_social AND   os_liq_liquidacion.id_liquidacion = os_liq_liquidacion_generada.id_liquidacion_generada AND os_liq_liquidacion.os_estado = 'L' AND os_liq_liquidacion_generada.id_liquidacion_generada  = ".$id_liquidacion_generada."
"));
         
return response()->json($res, 201);

}

public function getFacturaByMatricula(Request $request)
{
    $mat_matricula =$request->input('mat_matricula');  
    $res = DB::select( DB::raw("
    SELECT `id`, `mat_matricula`, `liq_liquidacion_detalle_id`, `fecha_subida`, `url`, id_liquidacion, CONCAT(mat_matricula.mat_apellido, ' ', mat_matricula.mat_nombre) as matricula_psicologo FROM `factura_liquidacion`, mat_matricula WHERE factura_liquidacion.mat_matricula = mat_matricula.mat_matricula_psicologo AND mat_matricula= ".$mat_matricula."
"));
         
return response()->json($res, 201);

}

public function getFacturaByLiquidacion(Request $request)
{
    $id_liquidacion =$request->input('id_liquidacion');  
    $res = DB::select( DB::raw("
    SELECT `id`, `mat_matricula`, `liq_liquidacion_detalle_id`, `fecha_subida`, `url`, id_liquidacion, CONCAT(mat_matricula.mat_apellido, ' ', mat_matricula.mat_nombre) as matricula_psicologo FROM `factura_liquidacion`, mat_matricula WHERE factura_liquidacion.mat_matricula = mat_matricula.mat_matricula_psicologo AND id_liquidacion= ".$id_liquidacion."
"));
         
return response()->json($res, 201);

}


public function getLiquidacionGenerada()
{    
    $res = DB::select( DB::raw("
    SELECT `id_liquidacion_generada`, `id_liquidacion`, `os_fecha`, `os_liq_estado` FROM `os_liq_liquidacion_generada` ORDER BY id_liquidacion_generada DESC
"));
         
return response()->json($res, 201);

}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getPadron(Request $request)
    {
        //echo "padron";

    
        $consulta =$request->input('consulta');        
        $valor =$request->input('valor');

        $res = DB::select( DB::raw("
        SELECT mat_matricula.mat_apellido, mat_matricula.mat_nombre, os_obra_social.os_nombre , mat_matricula_psicologo, mat_domicilio_laboral, mat_tel_laboral
        FROM mat_matricula, matricula_obra_social, os_obra_social 
        WHERE  matricula_obra_social.mat_matricula = mat_matricula.mat_matricula_psicologo and matricula_obra_social.obra_social_id = os_obra_social.id_obra_social 
        
        
        AND   ".$consulta."  LIKE '".$valor."%'
    "));
             
    return response()->json($res, 201);
      
    }




    

    /*** GENERO EL TOKEN DE TODOS LOS PSICOLOGOS  */
    public function actualizarPassword(Request $request)
    {       
        $result = DB::select( DB::raw(" 
         SELECT * FROM  users WHERE token_autorizacion = :token_autorizacion")
        , array(
            'token_autorizacion' => $request->input('token_autorizacion')
        ));

    $password =$request->input('password');
    $ret_password=bcrypt($password);

        $update = DB::table('users')->limit(1) 
        ->where('id',  $result[0]->id)
        ->update( [ 
         'password' => $ret_password,       
         'updated_at' => date("Y-m-d H:i:s")     ]); 

    

             
    return response()->json($update, 201);
      
    }


    

    /*** GENERO EL TOKEN DE TODOS LOS PSICOLOGOS  */
    public function generarTokenValidacion()
    {
       

        $result = DB::select( DB::raw("
        SELECT * FROM  users
    "));

    foreach ($result as $res) {

        $hashed_random_password = Hash::make(str_random(8));

        $update = DB::table('users')
        ->where('id',  $res->id)
        ->update( [ 
         'token_autorizacion' => $hashed_random_password,       
         'updated_at' => date("Y-m-d H:i:s")     ]); 

    }
             
    return response()->json($res, 201);
      
    }
}
