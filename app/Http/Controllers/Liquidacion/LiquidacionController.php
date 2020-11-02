<?php

namespace App\Http\Controllers\Liquidacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 


class LiquidacionController extends ApiController
{



    
  public function getLiquidacionByMatriculaAndEstado(Request $request)
  {      
    $mat_matricula = $request->input('mat_matricula');
    $estado = $request->input('estado');

    $res = DB::select( DB::raw("SELECT id_os_liq_orden, mat_matricula,CONCAT(mat_matricula.mat_apellido, ' ',mat_matricula.mat_nombre ) AS mat_apellido_nombre, os_liq_orden.id_sesion,  
   os_fecha, os_cantidad, os_precio_sesion, os_precio_total, os_estado_liquidacion, os_liq_numero, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo, pac_paciente.id_paciente,
   pac_paciente.pac_nombre, pac_paciente.pac_dni, pac_paciente.pac_dni
   FROM `os_liq_orden`, mat_matricula, os_obra_social, os_sesion, os_sesion_tipo, pac_paciente 
   WHERE os_liq_orden.mat_matricula = mat_matricula.mat_matricula_psicologo  
   AND os_liq_orden.id_obra_social = os_obra_social.id AND os_liq_orden.id_sesion = os_sesion.id_sesion 
   AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo AND os_liq_orden.id_paciente = pac_paciente.id_paciente 
   AND mat_matricula.mat_matricula_psicologo = :mat_matricula AND os_estado_liquidacion = :estado ORDER BY id_os_liq_orden DESC
    "),array('mat_matricula' => $mat_matricula,
               'estado' => $estado));
      
        return response()->json($res, "200");
  }


  public function getLiquidacionOrdenBetweenDates(Request $request)
  {          
    
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_desde'));
    $fecha_desde =  date('Y-m-d', strtotime($tmp_fecha));   
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_hasta'));
    $fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha)); 
    $estado = $request->input('estado');
    //echo $fecha_desde;

    $res = DB::select( DB::raw("SELECT id_os_liq_orden, mat_matricula,CONCAT(mat_matricula.mat_apellido, ' ',mat_matricula.mat_nombre ) AS mat_apellido_nombre, os_liq_orden.id_sesion,  
   os_fecha, os_cantidad, os_precio_sesion, os_precio_total, os_estado_liquidacion, os_liq_numero, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo, pac_paciente.id_paciente,
   pac_paciente.pac_nombre, pac_paciente.pac_dni, pac_paciente.pac_dni
   FROM `os_liq_orden`, mat_matricula, os_obra_social, os_sesion, os_sesion_tipo, pac_paciente 
   WHERE os_liq_orden.mat_matricula = mat_matricula.mat_matricula_psicologo  
   AND os_liq_orden.id_obra_social = os_obra_social.id AND os_liq_orden.id_sesion = os_sesion.id_sesion 
   AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo AND os_liq_orden.id_paciente = pac_paciente.id_paciente 
   AND os_fecha BETWEEN :fecha_desde AND :fecha_hasta AND os_estado_liquidacion = :estado ORDER BY id_os_liq_orden DESC
    "), array(
        'fecha_desde' =>$fecha_desde,
        'fecha_hasta' => $fecha_hasta,
        'estado' => $estado  
      ));
        return response()->json($res, "200");
  }

  public function auditarOrdenes(Request $request){
    $t =$request;
    $i = 0;
    while(isset($t[$i])){       
      $update = DB::table('os_liq_orden')         
      ->where('id_os_liq_orden',$t[$i]["id_os_liq_orden"]) ->limit(1) 
      ->update( [   
       'os_estado_liquidacion' => 'AUD',  
       //'usuario_audita_id' => $t[$i]["usuario_audita_id"],
            ]);  
          $i++;

    }

    return response()->json($i, "201");

}



  public function setOrden(Request $request) {

    $tmp_fecha = str_replace('/', '-', $request->fecha);
    $os_fecha =  date('Y-m-d', strtotime($tmp_fecha));   


    $id =    DB::table('os_liq_orden')->insertGetId([
      
      'mat_matricula' => $request->mat_matricula, 
      'id_obra_social' => $request->obra_social_id,    
      'id_sesion' => $request->sesion_id,    
      'id_paciente' => $request->paciente_id,
      'os_fecha' => $os_fecha,    
      'os_cantidad' => $request->cantidad,    
      'os_precio_sesion' => $request->precio_sesion,    
      'os_precio_total' => $request->precio_total,    
      'os_estado_liquidacion' => $request->estado_liquidacion,    
      'os_liq_numero' => $request->liq_numero    
      //'os_liq_fecha_presentacion' => $request->os_liq_fecha_presentacion    
    
  ]);    
    return response()->json($id, "200");  
  }

  public function putOrden(Request $request, $id)
  {

    $tmp_fecha = str_replace('/', '-', $request->input('os_fecha'));
    $os_fecha =  date('Y-m-d', strtotime($tmp_fecha));   

    $res =  DB::table('os_liq_orden')
    ->where('id_os_liq_orden', $id)
    ->update([        
      'mat_matricula' => $request->input('mat_matricula'),
      'id_obra_social' => $request->input('id_obra_social'),
      'id_sesion' => $request->input('id_sesion'),
      'id_paciente' => $request->input('id_paciente'),
      'os_fecha' => $os_fecha,
      'os_cantidad' => $request->input('os_cantidad'),
      'os_precio_sesion' => $request->input('os_precio_sesion'),
      'os_precio_total' => $request->input('os_precio_total'),
      'os_estado_liquidacion' => $request->input('os_estado_liquidacion'),
      'os_liq_numero' => $request->input('os_liq_numero')       
      ]);        
      return response()->json($res, "200"); 
  }


  

    public function setDeuda(Request $request) {

        $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_pago'));
        $mat_fecha_pago =  date('Y-m-d', strtotime($tmp_fecha));   
        $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_vencimiento'));
        $mat_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha));   
    
        $id =    DB::table('mat_pago_historico')->insertGetId([
          
          'mat_matricula' => $request->mat_matricula, 
          'mat_fecha_pago' => $mat_fecha_pago,    
          'mat_fecha_vencimiento' => $mat_fecha_vencimiento,    
          'mat_monto' => $request->mat_monto, mat_interes,    
          'mat_monto_cobrado' => $request->mat_monto_cobrado,    
          'mat_num_cuota' => $request->mat_num_cuota,    
          'mat_descripcion' => $request->mat_descripcion,    
          'mat_id_plan' => $request->mat_id_plan,    
          'id_concepto' => $request->id_concepto,    
          'mat_numero_comprobante' => $request->mat_numero_comprobante,    
          'mat_numero_recibo' => $request->mat_numero_recibo,    
          'mat_estado_recibo' => $request->mat_estado_recibo,    
          'mat_tipo_pago' => $request->mat_tipo_pago,    
          'mat_estado' => $request->mat_estado,    
          'id_usuario' => $request->id_usuario        
      ]);    
        return response()->json($id, "200");  
      }
      


      
    public function afectarOrdenes(Request $request)
    {
        $tmp_fecha = str_replace('/', '-', $request["os_fecha_desde"]);
        $os_fecha_desde =  date('Y-m-d', strtotime($tmp_fecha));    
        $tmp_fecha = str_replace('/', '-', $request["os_fecha_hasta"]);
        $os_fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha));    
        
    $id_os_liquidacion= DB::table('os_liq_liquidacion')->insertGetId([
        'id_os_obra_social' => $request["id_os_obra_social"],
        'os_liq_numero' => $request["os_liq_numero"],        
        'os_fecha_desde' => $os_fecha_desde,
        'os_fecha_hasta' => $os_fecha_hasta,
        'id_liquidacion' => 0,
        'os_cant_ordenes' => $request["os_cant_ordenes"],
        'os_monto_total' => $request["os_monto_total"],            
        'os_estado' => $request["os_estado"]
    ]);    
    
  $i = 0;
    while(isset($request->registros[$i])){       
        $update = DB::table('os_liq_orden')         
        ->where('id_os_liq_orden',$request->registros[$i]["id_os_liq_orden"] ) ->limit(1) 
        ->update( [            
         'os_liq_numero' =>$os_liq_numero,
         'os_estado_liquidacion'=>'AFE' ]);  
            $i++;

        }
        //echo  $request->registros[0]["id"];
      return response()->json($liquidacion_numero, 201);        
 
    }


        
    public function putExpediente(Request $request, $id)
    {                   
      $tmp_fecha = str_replace('/', '-', $request["os_fecha_desde"]);
        $os_fecha_desde =  date('Y-m-d', strtotime($tmp_fecha));    
        $tmp_fecha = str_replace('/', '-', $request["os_fecha_hasta"]);
        $os_fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha));    

        $t = $request['id'];

        $update = DB::table('os_liq_liquidacion') 
        ->where('id_os_liquidacion', $id) ->limit(1) 
        ->update( [ 
         
            'id_os_obra_social' => $request['id_os_obra_social'],                       
            'os_liq_numero' => $request['os_liq_numero'],     
            'os_fecha_desde' => $os_fecha_desde,    
            'os_fecha_hasta' =>$os_fecha_hasta,  
            'os_cant_ordenes' => $request['os_cant_ordenes'],      
            'os_monto_total' => $request['os_monto_total'],                  
            'os_estado' => $request['os_estado'],   
            'id_liquidacion' =>  $request['id_liquidacion']    ]); 

                  
           return response()->json($request, 201);    
    }

      public function desafectarExpediente(Request $request)
      {       
          $os_liq_numero =$request->input('os_liq_numero') ;
  
         $estado = DB::update( DB::raw("
         UPDATE os_liq_orden SET os_estado_liquidacion = 'AUD',  os_liq_numero = 0 WHERE os_estado_liquidacion = 'AFE' AND operacion_cobro.fecha_cobro  AND os_liq_numero= ".$os_liq_numero."
      "));         
        DB::table('os_liq_liquidacion')->where('id_os_liquidacion', '=', $os_liq_numero)->delete();
        return response()->json("registro desafectado", 201);      
      }
}
