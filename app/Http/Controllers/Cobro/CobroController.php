<?php

namespace App\Http\Controllers\Cobro;

use Illuminate\Http\Request;
use DateTime;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

// CONFIGURO EL MAXIMO TIEMPO DE EJECUCION
ini_set('max_execution_time', '1024');

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

    var $valorMatricula = 0;
    var $valorfondo = 0;
    var $valorMatriculaNuevo = 0;
    var $conceptoMatricula = 0;
/* -------------------------------------------------------------------------- */
/*                                PLAN DE PLAGO                               */
/* -------------------------------------------------------------------------- */

    public function getUltimoPlanPago()
    {
      $res = DB::select( DB::raw("SELECT max(`mat_id_plan`) as ultimo FROM `mat_pago_historico` WHERE 1
      "));

          return response()->json($res, "200");
    }



    public function setPlanPagoMatricula(Request $request) {

      $i = 0;
      $j = 0;
   //   var_dump($request->concepto[0]);
       while(isset($request->concepto[$i])){
        //echo 'concepto '.$request->concepto[$i]["mat_matricula"];
        $update = DB::table('mat_pago_historico')
        ->where('id_pago_historico', $request->concepto[$i]['id_pago_historico']) ->limit(1)
        ->update( [
        'mat_estado' => 'P',
        'mat_fecha_pago' => $request->concepto[$i]['mat_fecha_pago'],
        'mat_id_plan' => $request->concepto[$i]['mat_id_plan'],
        'mat_tipo_pago' => $request->concepto[$i]['mat_tipo_pago'],
        'id_usuario' => $request->concepto[$i]['id_usuario']
          ]);
        $i++;
      }

      while(isset($request->plan[$j])){


        $id =    DB::table('mat_pago_historico')->insertGetId([

          'mat_matricula' => $request->plan[$j]['mat_matricula'],
          'mat_fecha_pago' => $request->plan[$j]['mat_fecha_pago'],
          'mat_fecha_vencimiento' => $request->plan[$j]['mat_fecha_vencimiento'],
          'mat_monto' => $request->plan[$j]['mat_monto'],
          'mat_monto_cobrado' => $request->plan[$j]['mat_monto_cobrado'],
          'mat_num_cuota' => $request->plan[$j]['mat_num_cuota'],
          'mat_descripcion' => $request->plan[$j]['mat_descripcion'],
          'mat_id_plan' => $request->plan[$j]['mat_id_plan'],
          'id_concepto' => $request->plan[$j]['id_concepto'],
          'mat_numero_comprobante' => $request->plan[$j]['mat_numero_comprobante'],
          'mat_numero_recibo' => 0,
          'mat_estado_recibo' => 'A',
          'mat_tipo_pago' => $request->plan[$j]['mat_tipo_pago'],
          'mat_estado' => $request->plan[$j]['mat_estado'],
          'id_usuario' => $request->plan[$j]['id_usuario']
      ]);

      //  echo 'plan '. $request->plan[$j]["mat_matricula"];
        $j++;
      }
      return response()->json('ok', "200");
    }


/* -------------------------------------------------------------------------- */
/*                                  CONCEPTOS                                 */
/* -------------------------------------------------------------------------- */


    public function getConcepto(Request $request)
    {
      $res = DB::select( DB::raw("SELECT `id_concepto`, `mat_concepto`, `mat_monto`, `mat_interes`, `mat_descripcion` FROM `mat_concepto` WHERE 1
      "));

          return response()->json($res, "200");
    }




    public function setConcepto(Request $request) {

      $id =    DB::table('mat_concepto')->insertGetId([

        'mat_concepto' => $request->mat_concepto,
        'mat_monto' => $request->mat_monto,
        'mat_interes' => $request->mat_interes,
        'mat_descripcion' => $request->mat_descripcion
    ]);
      return response()->json($id, "200");
    }


    public function putConcepto(Request $request, $id)
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


      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion,
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  , users
      WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
      AND users.id = mat_pago_historico.id_usuario
      AND mat_matricula.mat_matricula_psicologo = :mat_matricula
      "),array('mat_matricula' => $mat_matricula));

          return response()->json($res, "200");
    }

    public function getDeudaByMatriculaAndEstado(Request $request)
    {
        $mat_matricula = $request->input('mat_matricula');
        $estado = $request->input('estado');

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion,
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
      FROM `mat_pago_historico`, mat_concepto, mat_matricula   , users
      WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
      AND users.id = mat_pago_historico.id_usuario
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
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
      FROM `mat_pago_historico`, mat_concepto, mat_matricula , users
      WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
      AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
      AND users.id = mat_pago_historico.id_usuario
      AND mat_matricula.mat_matricula_psicologo =  :mat_matricula
      AND mat_id_plan = :mat_id_plan
      "),array('mat_matricula' => $mat_matricula,
               'mat_id_plan' => $mat_id_plan));

          return response()->json($res, "200");
    }



    public function getPlanes(Request $request)
    {

      $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion,
      mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
      FROM `mat_pago_historico`, mat_concepto, mat_matricula  , users
      WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
      AND users.id = mat_pago_historico.id_usuario
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
        mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto, nombreyapellido
        FROM `mat_pago_historico`, mat_concepto, mat_matricula,users
        WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
        AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
        AND users.id = mat_pago_historico.id_usuario
        AND mat_Fecha_vencimiento BETWEEN :fecha_desde AND :fecha_hasta
      "), array(
          'fecha_desde' =>$fecha_desde,
          'fecha_hasta' => $fecha_hasta
        ));
      }

      if ($estado === 'A') {

        $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion,
        mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
        FROM `mat_pago_historico`, mat_concepto, mat_matricula, users
        WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
        AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
        AND users.id = mat_pago_historico.id_usuario
        AND mat_estado = :estado
        AND mat_Fecha_vencimiento BETWEEN :fecha_desde AND :fecha_hasta
      "), array(
          'fecha_desde' =>$fecha_desde,
          'fecha_hasta' => $fecha_hasta,
          'estado' => $estado
        ));

      }

      if ($estado === 'P'){

        $res = DB::select( DB::raw("SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_interes, mat_pago_historico.mat_descripcion,
        mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario , mat_pago_historico.id_concepto, mat_concepto , nombreyapellido
        FROM `mat_pago_historico`, mat_concepto, mat_matricula, users
        WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
        AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
        AND users.id = mat_pago_historico.id_usuario
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




    public function putDeuda(Request $request, $id)
    {

      $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_pago'));
      $mat_fecha_pago =  date('Y-m-d', strtotime($tmp_fecha));
      $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_vencimiento'));
      $mat_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha));
  //    echo '-'. $request->input('mat_monto'). '-';
  echo $id;
  echo $request->input('mat_monto_final');
      $res =  DB::table('mat_pago_historico')
      ->where('id_pago_historico', $id)
      ->update([

        'id_concepto' => $request->input('id_concepto'),
        'mat_fecha_pago' => $mat_fecha_pago,
        'mat_fecha_vencimiento' => $mat_fecha_vencimiento,
        'mat_monto' => $request->input('mat_monto'),
        'mat_tipo_pago' => $request->input('mat_interes'),
        'mat_monto_cobrado' => $request->input('mat_monto_final'),
        'mat_num_cuota' => $request->input('mat_num_cuota'),
        'mat_descripcion' => $request->input('mat_descripcion'),
        'mat_id_plan' => $request->input('mat_id_plan'),
        'mat_numero_comprobante' => $request->input('mat_numero_comprobante'),
        'mat_numero_recibo' => $request->input('mat_numero_comprobante'),
        'mat_estado_recibo' => '',
        'mat_tipo_pago' => $request->input('mat_tipo_pago'),
        'mat_estado' => $request->input('mat_estado')

       // ,'id_usuario' => $request->input('id_usuario')
        ]);
        return response()->json($res, "200");
    }



    public function setDeuda(Request $request) {

      $tmp_fecha = str_replace('/', '-', $request->mat_fecha_pago);
      $mat_fecha_pago =  date('Y-m-d', strtotime($tmp_fecha));
      $tmp_fecha = str_replace('/', '-', $request->mat_fecha_vencimiento);
      $mat_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha));

      $id =    DB::table('mat_pago_historico')->insertGetId([

        'mat_matricula' => $request->mat_matricula,
        'mat_fecha_pago' => $mat_fecha_pago,
        'mat_fecha_vencimiento' => $mat_fecha_vencimiento,
        'mat_monto' => $request->mat_monto,
        'mat_monto_cobrado' => $request->mat_monto_cobrado,
        'mat_num_cuota' => $request->mat_num_cuota,
        'mat_descripcion' => $request->mat_descripcion,
        'mat_id_plan' => $request->mat_id_plan,
        'id_concepto' => $request->id_concepto,
        'mat_numero_comprobante' => $request->mat_numero_comprobante,
        'mat_numero_recibo' => $request->mat_numero_recibo,
        'mat_estado_recibo' => 'A',
        'mat_tipo_pago' => $request->mat_tipo_pago,
        'mat_estado' => $request->mat_estado,
        'id_usuario' => $request->id_usuario
    ]);
      return response()->json($id, "200");
    }


    public function setDeudaRegistros(Request $request) {


      $i = 0;

      while(isset($request[$i])){
         $tmp_fecha = str_replace('/', '-', $request[$i]['mat_fecha_pago']);
        $mat_fecha_pago =  date('Y-m-d', strtotime($tmp_fecha));
        $tmp_fecha = str_replace('/', '-', $request[$i]['mat_fecha_vencimiento']);
        $mat_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha));
        $id =    DB::table('mat_pago_historico')->insertGetId([

          'mat_matricula' => $request[$i]['mat_matricula'],
          'mat_fecha_pago' => $mat_fecha_pago,
          'mat_fecha_vencimiento' => $mat_fecha_vencimiento,
          'mat_monto' => $request[$i]['mat_monto'],
          'mat_monto_cobrado' => $request[$i]['mat_monto_cobrado'],
          'mat_num_cuota' => $request[$i]['mat_num_cuota'],
          'mat_descripcion' => $request[$i]['mat_descripcion'],
          'mat_id_plan' => $request[$i]['mat_id_plan'],
          'id_concepto' => $request[$i]['id_concepto'],
          'mat_numero_comprobante' => $request[$i]['mat_numero_comprobante'],
          'mat_numero_recibo' => 0,
          'mat_estado_recibo' => 'A',
          'mat_tipo_pago' => $request[$i]['mat_tipo_pago'],
          'mat_estado' => $request[$i]['mat_estado'],
          'id_usuario' => $request[$i]['id_usuario']
      ]);

      $i++;
     }


      return response()->json($id, "200");
    }






    public function putRegistroCobro(Request $request, $id)
    {
     //   $tmp_fecha = str_replace('/', '-', $request["os_fecha_desde"]);
     //   $os_fecha_desde =  date('Y-m-d', strtotime($tmp_fecha));
     //   $tmp_fecha = str_replace('/', '-', $request["os_fecha_hasta"]);
     //   $os_fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha));


  $i = 0;
    while(isset($request[$i])){
   // echo  $request[$i]["mat_monto"];
        $tmp_fecha = str_replace('/', '-', $request[$i]["mat_fecha_pago"]);
        $mat_fecha_pago =  date('Y-m-d', strtotime($tmp_fecha));

        $update = DB::table('mat_pago_historico')
        ->where('id_pago_historico', $request[$i]['id_pago_historico']) ->limit(1)
        ->update( [

        'mat_fecha_pago' => $mat_fecha_pago,
        'mat_monto' => $request[$i]['mat_monto'],
        'mat_monto_cobrado' => $request[$i]['mat_monto'],
        'mat_numero_comprobante' => $request[$i]['mat_numero_comprobante'],
        'mat_numero_recibo' => $request[$i]['mat_numero_comprobante'],
        'mat_tipo_pago' => $request[$i]['mat_tipo_pago'],
        'mat_estado' => $request[$i]['mat_estado'],
        'id_usuario' => $request[$i]['id_usuario']
          ]);
            $i++;

        }
        //echo  $request->registros[0]["id"];
   //   return response()->json($liquidacion_numero, 201);

    }




  public function generarDeudaPsicologo(Request $request) {

    $_anio =  date('Y/n/d', strtotime( $request->input('anio')));

    $mat_matricula_psicologo = $request->input('mat_matricula_psicologo');
    $consulta = $request->input('consulta');
    //echo  date('Y-m-d', strtotime(date(''.$anio.'-01-01')));
    $anio =  date('Y', strtotime(date($_anio)));
    //echo $anio;
    $this->getConceptoAGenerar();

    if($consulta === 'todos') {

        $periodoInicial = 0;
        $psicologo = DB::select( DB::raw("SELECT mat_matricula_psicologo, mat_fecha_egreso
        FROM mat_matricula
        WHERE mat_estado_matricula = 'A'
        ORDER BY  mat_matricula_psicologo ASC
        "));

        // VERIFICO QUE EL PSICOLOGO NO TENGA DEUDA GENERADA PREVIAMENTE

        for($i = 0; $i< count($psicologo); $i++){
           // echo $psicologo[$i]->mat_matricula_psicologo;
           if($this->validarDeudaMatricula($anio,$psicologo[$i]->mat_matricula_psicologo)) {
            $this->setDeudaRegistrosMatricula($psicologo[$i]->mat_matricula_psicologo, $psicologo[$i]->mat_fecha_egreso, $anio, $periodoInicial);
            }
        }

     }

    if($consulta === 'psicologo'){
        $periodoInicial = 0;
        $psicologo = DB::select( DB::raw("SELECT mat_matricula_psicologo, mat_fecha_egreso
        FROM mat_matricula
        WHERE mat_estado_matricula = 'A'
        AND mat_matricula_psicologo = '".$mat_matricula_psicologo."'
        ORDER BY  mat_matricula_psicologo ASC
        "));
        // VERIFICO QUE EL PSICOLOGO NO TENGA DEUDA GENERADA PREVIAMENTE
        if($this->validarDeudaMatricula($anio,$psicologo[0]->mat_matricula_psicologo)) {
            $this->setDeudaRegistrosMatricula($psicologo[0]->mat_matricula_psicologo, $psicologo[0]->mat_fecha_egreso, $anio, $periodoInicial);
        }
    }


    if($consulta === 'psicologofecha'){
        $periodoInicial =  date('n', strtotime(date($_anio)));
       // echo $periodoInicial;
        $psicologo = DB::select( DB::raw("SELECT mat_matricula_psicologo, mat_fecha_egreso
        FROM mat_matricula
        WHERE mat_estado_matricula = 'A'
        AND mat_matricula_psicologo = '".$mat_matricula_psicologo."'
        ORDER BY  mat_matricula_psicologo ASC
        "));
        // VERIFICO QUE EL PSICOLOGO NO TENGA DEUDA GENERADA PREVIAMENTE
        if($this->validarDeudaMatricula($anio,$psicologo[0]->mat_matricula_psicologo)) {
            $this->setDeudaRegistrosMatricula($psicologo[0]->mat_matricula_psicologo, $psicologo[0]->mat_fecha_egreso, $anio, $periodoInicial-1);
        }
    }

    if($consulta === 'fondo') { }


//echo $psicologo[0]->mat_matricula_psicologo;



        return response()->json($psicologo, "200");
  }


  // VALIDO SI LA MATRICULA TIENE DEUDA YA GENERADA

  private function validarDeudaMatricula($anio, $mat_matricula){
    //echo strtotime(date('Y-01-01'));
   $fecha_desde  = date('Y-m-d', strtotime(date(''.$anio.'-01-01')));
   $fecha_hasta =   date('Y-m-d', strtotime(date(''.$anio.'-12-31')));
    $psicologo = DB::select( DB::raw("SELECT COUNT(*) AS cont  FROM `mat_pago_historico`
    WHERE `mat_matricula` = '".$mat_matricula."'
    AND mat_fecha_vencimiento
    AND id_concepto IN(1,2,10)
    AND mat_fecha_vencimiento
    BETWEEN '".$fecha_desde."'  AND '".$fecha_hasta."'
    ORDER BY `id_pago_historico`  DESC
    "));
    if($psicologo[0]->cont === 0) {
    // devuelvo true ya que no posee deuda
    return true;
    } else {
     // devuelvo false por que ya posee deuda
     return false;
    }
  // echo $psicologo[0]->cont;

  }


  private function getConceptoAGenerar() {


    $res = DB::select( DB::raw("SELECT mat_monto FROM mat_concepto WHERE id_concepto IN(1,2,10)
    "));

    //var_dump($res);
    $this->valorMatricula = $res[0]->mat_monto;
    $this->valorfondo = $res[1]->mat_monto;
    $this->valorMatriculaNuevo = $res[2]->mat_monto;


  }


  private function setDeudaRegistrosMatricula($mat_matricula, $fechaMatricula, $anio, $periodoInicial) {

    $conceptoTotal = '';
    $_valorMatricula = 0;

    for($i = $periodoInicial; $i<12; $i++){
        $fecha_vencimiento  = date('Y-m-d', strtotime(date(''.$anio.'-'.($i+1).'-10')));

      $conceptoTotal =  $this->esAnioGracia($fecha_vencimiento, $fechaMatricula);

      if($conceptoTotal === 'bonificada') {
          $_valorMatricula = $this->valorMatriculaNuevo;
          $this->conceptoMatricula = 10;


        // FONDO SOLIDARIO LO PAGAN TODOS???
   // INSERTO MATRICULA

      $fecha_vencimiento  = date('Y-m-d', strtotime(date(''.$anio.'-'.($i+1).'-10')));
      $id =    DB::table('mat_pago_historico')->insertGetId([
        'mat_matricula' => $mat_matricula,
        'mat_fecha_pago' => '2099-12-31',
        'mat_fecha_vencimiento' => $fecha_vencimiento,
        'mat_monto' => $_valorMatricula,
        'mat_monto_cobrado' => $_valorMatricula,
        'mat_num_cuota' => $i+1,
        'mat_descripcion' => 'MATRICULA BONIFICADA',
        'mat_id_plan' => 0,
        'id_concepto' => 10,
        'mat_numero_comprobante' => 0,
        'mat_numero_recibo' => 0,
        'mat_estado_recibo' => 'A',
        'mat_tipo_pago' => 'C',
        'mat_estado' => 'A',
        'id_usuario' => '1'
    ]);

      }

      if($conceptoTotal === 'regular') {

        $_valorMatricula = $this->valorMatricula;
        $this->conceptoMatricula = 1;


        // FONDO SOLIDARIO LO PAGAN TODOS???
   // INSERTO MATRICULA

      $fecha_vencimiento  = date('Y-m-d', strtotime(date(''.$anio.'-'.($i+1).'-10')));
      $id =    DB::table('mat_pago_historico')->insertGetId([
        'mat_matricula' => $mat_matricula,
        'mat_fecha_pago' => '2099-12-31',
        'mat_fecha_vencimiento' => $fecha_vencimiento,
        'mat_monto' => $_valorMatricula,
        'mat_monto_cobrado' => $_valorMatricula,
        'mat_num_cuota' => $i+1,
        'mat_descripcion' => 'MATRICULA',
        'mat_id_plan' => 0,
        'id_concepto' => 1,
        'mat_numero_comprobante' => 0,
        'mat_numero_recibo' => 0,
        'mat_estado_recibo' => 'A',
        'mat_tipo_pago' => 'C',
        'mat_estado' => 'A',
        'id_usuario' => '1'
    ]);

      }

      if($conceptoTotal === 'excenta') {



      }


    // INSERTO FONDO SOLIDARIO

    $id =    DB::table('mat_pago_historico')->insertGetId([
        'mat_matricula' => $mat_matricula,
        'mat_fecha_pago' => '2099-12-31',
        'mat_fecha_vencimiento' => $fecha_vencimiento,
        'mat_monto' => $this->valorfondo,
        'mat_monto_cobrado' => $this->valorfondo,
        'mat_num_cuota' => $i+1,
        'mat_descripcion' => 'FONDO SOLIDARIO',
        'mat_id_plan' => 0,
        'id_concepto' => 2,
        'mat_numero_comprobante' => 0,
        'mat_numero_recibo' => 0,
        'mat_estado_recibo' => 'A',
        'mat_tipo_pago' => 'C',
        'mat_estado' => 'A',
        'id_usuario' => '1'
    ]);
   }


 //   return response()->json($id, "200");
  }

  private function esAnioGracia($mesAvalidar, $fechMatricula) {
    echo date($mesAvalidar).' - ';
    echo date($fechMatricula).'  ';


    $first_date = date($mesAvalidar);
    $second_date = date($fechMatricula);
    $d1 = new DateTime($mesAvalidar); // FECHA ACTUAL
    $d2 = new DateTime($fechMatricula); // FECHA MATRICULA
    $difference = $d2->diff($d1);
    echo ' diferencia ->'. $difference->days. ' ';
   // var_dump($difference);
  //  echo ' diferencia ->'. $difference->m. ' ';
// FALTA DEFINIR QUE SEA MAYOR A DOS AÑOS, SOLO  ESTA TOMANDO EL AÑO DE GRACIA Y EL AÑO PRIMERO

    if(($difference->days <= 1095)&&($difference->days > 365)){
        echo ' matricula bonificada |';
        return 'bonificada';
    }

    if($difference->days > 1095){
        echo ' matricula regular |';
        return 'regular';
    }

    return 'excenta';

  }

}
