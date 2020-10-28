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
    *   usuario_id
    *   mat_pago_historico  agregar campo porcentaje para poder calcular el valor cobrado
    *   mat_pago_historico cambiar id_usuario por usuario_id
    *   mat_pago_historico quitar mat_numero recibo
    *   mat_pago_historico quitar mat_estado_recibo
   
    */


    public function getConcepto(Request $request)
    {      
      $res = DB::select( DB::raw("SELECT `id_concepto`, `mat_concepto`, `mat_monto`, `mat_interes`, `mat_descripcion` FROM `mat_concepto` WHERE 1
      "));
      
          return response()->json($res, "200");
    }

    public function setConcepto(Request $request) {

      $id =    DB::table('mat_concepto')->insertGetId([
        
        'mat_concepto' => $request->mat_concepto, 
        'mat_monto' => $mat_monto,    
        'mat_interes' => $mat_interes,    
        'mat_descripcion' => $request->mat_descripcion       
    ]);    
      return response()->json($id, "200");  
    }


    public function putDeuda(Request $request, $id)
    {
      $res =  DB::table('mat_concepto')
      ->where('id_concepto', $id)
      ->update([        
        'mat_concepto' => $request->input('mat_concepto'),
        'mat_monto' => $request->input('mat_monto'),
        'mat_interes' => $request->input('mat_interes'),
        'mat_descripcion' => $request->input('mat_descripcion')        
        ]);        
        return response()->json($res, "200"); 
    }



    public function setConceptoToPsicologo(Request $request) {

      $id =    DB::table('mat_concepto')->insertGetId([
        
        'mat_concepto' => $request->mat_concepto, 
        'mat_monto' => $mat_monto,    
        'mat_interes' => $mat_interes,    
        'mat_descripcion' => $request->mat_descripcion       
    ]);    
      return response()->json($id, "200");  
    }

    
    public function getDeudaByMatricula(Request $request)
    {      
        $mat_matricula = $request->input('mat_matricula');
        $estado = $request->input('estado');

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto 
      AND mat_matricula.mat_matricula_psicologo = :mat_matricula
      "),array('mat_matricula' => $mat_matricula));
      
          return response()->json($res, "200");
    }

    public function getDeudaByMatriculaAndEstado(Request $request)
    {      
        $mat_matricula = $request->input('mat_matricula');
        $estado = $request->input('estado');

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
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
      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
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

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
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
      if ($estado === 'todos') {
        $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
        mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto 
        FROM `mat_pago_historico`, mat_concepto, mat_matricula  WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo 
        AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
        AND mat_fecha_pago BETWEEN :fecha_desde AND :fecha_hasta
      "), array(
          'fecha_desde' =>$fecha_desde,
          'fecha_hasta' => $fecha_hasta
        ));
      } else {

        $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion, 
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

      }

     
      
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

}
