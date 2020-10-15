<?php

namespace App\Http\Controllers\Cobro;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 

class CobroController extends ApiController
{

    /* MIGRACIONES
     
    mat_pago_historico :

    quitar mat_monto_cobrado
    cambiar :
    *  mat_id_plan por id_plan
    *   quitar mat_numero_recibo
    *   quitar mat_estado_recibo
    *   reemplazar mat_tipo_pago y por tipo_pago_id y su numero
    *   cambiar mat_estado por estado
    *   cambiar id_usuario por usuario_id
    *   cambiar id_concepto por concepto_id
    */
    
    public function getDeudaByMatricula(Request $request)
    {      
        $mat_matricula = $request->input('mat_matricula');
        $estado = $request->input('estado');
      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_pago_historico.mat_descripcion, 
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto 
      AND mat_matricula.mat_matricula_psicologo = :mat_matricula
      AND mat_estado = :estado
      "),array('mat_matricula' => $mat_matricula,
               'estado' => $estado));
      
          return response()->json($res, "200");
    }


    public function getDeudaByPlanAndMatricula(Request $request)
    {      
        $mat_matricula = $request->input('mat_matricula');
        $mat_id_plan = $request->input('mat_id_plan');
      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_pago_historico.mat_descripcion, 
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto 
      AND mat_matricula.mat_matricula_psicologo =  :mat_matricula
      AND mat_id_plan = :mat_id_plan
      "),array('mat_matricula' => $mat_matricula,
               'mat_id_plan' => $mat_id_plan));
      
          return response()->json($res, "200");
    }

    public function getPlanes(Request $request)
    {      

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_pago_historico.mat_descripcion, 
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto       
      AND mat_id_plan !=0 GROUP BY mat_pago_historico.mat_id_plan
      "));
      
          return response()->json($res, "200");
    }


    public function getDeudaBydMatriculaBetweenDates(Request $request)
    {      
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_desde'));
        $fecha_desde =  date('Y-m-d', strtotime($tmp_fecha));  
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_hasta'));
        $fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha));    
        $estado = $request->input('estado');


      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_pago_historico.mat_descripcion, 
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
      AND mat_estado = :estado
      AND mat_fecha_pago BETWEEN :fecha_desde AND :fecha_hasta
    "), array(
        'fecha_desde' =>$fecha_desde,
        'fecha_hasta' => $fecha_hasta,
        'estado' => $estado 
      ));
      
          return response()->json($res, "200");
    }

}
