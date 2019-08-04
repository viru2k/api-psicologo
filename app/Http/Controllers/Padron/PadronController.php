<?php

namespace App\Http\Controllers\Padron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Auth\AuthenticationException; 
use App\Http\Controllers\ApiController;

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
   $this->middleware('auth:api', ['only' => ['getLiquidacionByPsicologo', 'getLiquidacionNumero']]); 

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
    SELECT id_os_liquidacion, id_os_obra_social, os_liq_numero, os_fecha_desde, os_fecha_hasta, os_cant_ordenes, os_monto_total, os_estado, id_liquidacion FROM os_liq_liquidacion
"));
         
return response()->json($res, 201);

}


public function getLiquidacionByPsicologo(Request $request)
{
    $mat_matricula =$request->input('mat_matricula');  
    $res = DB::select( DB::raw("
    SELECT `id_liquidacion_detalle`, `mat_matricula`, `os_liq_bruto`, `os_ing_brutos`, `os_lote_hogar`, `os_gasto_admin`, `os_imp_cheque`, `os_descuentos`, `os_desc_matricula`, `os_desc_fondo_sol`, `os_otros_ing_eg`, `os_liq_neto`, `num_comprobante`, `os_num_ing_bruto`,os_liq_liquidacion_generada.id_liquidacion FROM `os_liq_liquidacion_detalle`, os_liq_liquidacion_generada WHERE os_liq_liquidacion_detalle.id_liquidacion_generada = os_liq_liquidacion_generada.id_liquidacion_generada  AND mat_matricula =  ".$mat_matricula."   
    ORDER BY os_liq_liquidacion_generada.id_liquidacion_generada DESC
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
        SELECT mat_matricula.mat_apellido, mat_matricula.mat_nombre, os_obra_social.os_nombre , mat_matricula_psicologo
        FROM mat_matricula, matricula_obra_social, os_obra_social 
        WHERE  matricula_obra_social.mat_matricula = mat_matricula.mat_matricula_psicologo and matricula_obra_social.obra_social_id = os_obra_social.id_obra_social 
        
        
        AND   ".$consulta."  LIKE '".$valor."%'
    "));
             
    return response()->json($res, 201);
      
    }
}
