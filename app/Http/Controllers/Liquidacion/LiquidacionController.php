<?php

namespace App\Http\Controllers\Liquidacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 


class LiquidacionController extends ApiController
{

var $avance_liquidacion = 0;  
// ARREGLOS
var $matriculados;
var $conceptos;
var $deuda;
var $percepcion;
var $liquidacionDetalle;

// VALORES GENERALES

var $concepto;
var $bruto = 0; // valor de las ordenes
var $neto = 0; // valor final despues de deducciones
var $importe = 0; // valor del concepto
var $saldo = 0; // despues de cada de deduccion va decreciendo , si es 0 no pueden seguir realizando deducciones
var $tieneSaldo = false; 
var $pago_historico_ids = '';
var $INTERESES_pago_historico_id = '';
var $interes = 0;


// CONCEPTOS A DEDUCIR
var $os_int_mora = 0;
var $os_ing_brutos_limite = 0;
var $os_ing_brutos = 0;
var $os_lote_hogar = 0;
var $os_gasto_admin = 0;
var $os_imp_cheque = 0;
var $os_descuentos = 0; // valores cargados a mano para realizar correcciones , debitos  o creditos
var $os_desc_matricula = 0; // descuentos de matricula
var $os_desc_fondo_sol = 0; // descuentos de fondo solidario
var $os_otros_ing_eg = 0; // otros movimientos

// VALORES CALCULADOS

var $TOTAL_os_int_mora = 0;
var $TOTAL_os_ing_brutos_limite = 0;
var $TOTAL_os_ing_brutos = 0;
var $TOTAL_os_lote_hogar = 0;
var $TOTAL_os_gasto_admin = 0;
var $TOTAL_os_imp_cheque = 0;
var $TOTAL_PERCEPCIONES = 0;
var $TOTAL_os_descuentos = 0; // valores cargados a mano para realizar correcciones , debitos  o creditos
var $TOTAL_os_desc_matricula = 0; // descuentos de matricula
var $TOTAL_os_desc_fondo_sol = 0; // descuentos de fondo solidario
var $TOTAL_os_otros_ing_eg = 0; // otros movimientos


/* -------------------------------------------------------------------------- */
/*                                  LIQUIDAR                                  */
/* -------------------------------------------------------------------------- */

public function liquidar(Request $request)
{     
  $_registros = 0;
  $id_liquidacion_generada = $request->input('id_liquidacion_generada');
  //$matriculados = $this->obtenerMatriculas(); POR AHORA NO ES NECESARIO
  $this->liquidacionDetalle = $this->obtenerLiquidacionDetalle($id_liquidacion_generada); // DEBE VENIR DEL REQUEST
  $this->concepto = $this->obtenerConcepto();
  $this->percepcion = $this->obtenerPercepcion();

  $_registros = count($this->liquidacionDetalle);

   //

/* --------------- echo $matriculados[0]->mat_nombre_apellido; -------------- */
/* ------------------------ var_dump($matriculados); ------------------------ */


    foreach ($this->liquidacionDetalle as $index => $_liquidacionDetalle) {
      
      $avance_liquidacion = ($index * 100) /$_registros;

/* ---------- GUARDO EL BRUTO PARA PODER IR REALIZANDO DEDUCCIONES ---------- */
    $this->saldo = $_liquidacionDetalle->os_liq_bruto;

/* ---------------- CALCULO LAS RETENCIONES DE CADA MATRICULA --------------- */
    //echo $_liquidacionDetalle->mat_matricula;   
      if($_liquidacionDetalle->mat_matricula) {
        
        // CALCULO LOS CONCEPTOS
      $this->TOTAL_PERCEPCIONES =   $this->calcularPercepciones($_liquidacionDetalle);

      $this->saldo = $this->saldo - $this->TOTAL_PERCEPCIONES;    
        // OBTENGO LA DEUDA Y PRUEBO DESCONTAR
        $this->deuda = $this->obtenerDeudaMatricula($_liquidacionDetalle->mat_matricula);   
        
        $this->pago_historico_ids = $this->calcularDeudaMatricula();
        
        if($this->pago_historico_ids !== ''){ // SI NO VIENE VACIO ACTUALIZO LOS VALORES DE MATRICULA
          $this->actualizarDeudaLiquidacion($this->pago_historico_ids, $_liquidacionDetalle->id_liquidacion_detalle, $_liquidacionDetalle->mat_matricula );

        }
      }
      // ACTUALIZO TODAS LAS DEDUCCIONES
      $this->actualizarLiquidacionDetalleConceptos($_liquidacionDetalle->id_liquidacion_detalle);
      // LIMPIAR DATOS DEL PSICOLOGO
      $this->limpiarDatos();
    }

    if($this->INTERESES_pago_historico_id !== ''){ // SI NO VIENE VACIO ACTUALIZO LOS VALORES DE MATRICULA
      $this->actualizarDeudaLiquidacionInteres($this->INTERESES_pago_historico_id);
    }
    $this->INTERESES_pago_historico_id = '';
echo 'resuelto';

    
}

private function limpiarDatos() {

    
  $this->bruto = 0; 
  $this->neto = 0; 
  $this->importe = 0; 
  $this->saldo = 0; 
  $this->tieneSaldo = false; 
  $this->pago_historico_ids = '';
  $this->interes = 0;
  // CONCEPTOS A DEDUCIR  
  $this->os_descuentos = 0; 
  $this->os_desc_matricula = 0; 
  $this->os_desc_fondo_sol = 0;
  $this->os_otros_ing_eg = 0; 
  // VALORES CALCULADOS
  $this->TOTAL_os_int_mora = 0;
  $this->TOTAL_os_ing_brutos = 0;
  $this->TOTAL_os_lote_hogar = 0;
  $this->TOTAL_os_gasto_admin = 0;
  $this->TOTAL_os_imp_cheque = 0;
  $this->TOTAL_PERCEPCIONES = 0;
  $this->TOTAL_os_descuentos = 0; 
  $this->TOTAL_os_desc_matricula = 0; 
  $this->TOTAL_os_desc_fondo_sol = 0; 
  $this->TOTAL_os_otros_ing_eg = 0; 

}


/* -------------------------------------------------------------------------- */
/*                                  FIN LIQUIDAR                              */
/* -------------------------------------------------------------------------- */



/* -------------------------------------------------------------------------- */
/*    OBTENGO LOS DATOS PRIMARIOS : MATRICULAS, CONCEPTOS , DEUDA, PERCEPCION */
/* -------------------------------------------------------------------------- */

  private function obtenerMatriculas() {

    $res = DB::select( DB::raw("SELECT id, mat_matricula_psicologo, CONCAT(mat_apellido, ' ', mat_nombre) AS mat_nombre_apellido 
    FROM mat_matricula WHERE mat_estado_matricula = 'A' ORDER BY mat_matricula_psicologo ASC
    "));
    return $res;
  }


  private function obtenerPercepcion() {

    $res = DB::select( DB::raw("SELECT id_liquidacion_detalle, os_liq_detalle, os_liq_monto_porcentaje, os_liq_tipo FROM os_liq_percepcion WHERE 1
    "));

    

    foreach ($res as $index => $registro) {

      if($registro->id_liquidacion_detalle === 9){
        $this->os_gasto_admin = $registro->os_liq_monto_porcentaje;
      }
      if($registro->id_liquidacion_detalle === 13){
        
        $this->os_ing_brutos = $registro->os_liq_monto_porcentaje;
     
      }
      if($registro->id_liquidacion_detalle === 14){
        $this->os_lote_hogar = $registro->os_liq_monto_porcentaje;
      }
      if($registro->id_liquidacion_detalle === 15){
        $this->os_imp_cheque = $registro->os_liq_monto_porcentaje;
      }
      if($registro->id_liquidacion_detalle === 17){
        $this->os_int_mora = $registro->os_liq_monto_porcentaje;
      }
      if ($registro->id_liquidacion_detalle === 18){
      
        $this->TOTAL_os_ing_brutos_limite = $registro->os_liq_monto_porcentaje;
        
      }

    }
    return $res;
  }


  private function obtenerLiquidacionDetalle($id_liquidacion_generada) {

    $res = DB::select( DB::raw("SELECT id_liquidacion_detalle, mat_matricula, 
    os_liq_bruto, os_ing_brutos, os_lote_hogar, os_gasto_admin, os_imp_cheque, 
    os_descuentos, os_desc_matricula, os_desc_fondo_sol, 
    os_otros_ing_eg, os_liq_neto, num_comprobante, 
    os_num_ing_bruto, id_liquidacion_generada 
    FROM os_liq_liquidacion_detalle     
    WHERE 
    id_liquidacion_generada = :id_liquidacion_generada  
     "),array( 'id_liquidacion_generada' => $id_liquidacion_generada));              
    return $res;
  }


  private function obtenerDeudaMatricula($mat_matricula) {

   // echo $mat_matricula;
    $res = DB::select( DB::raw("(SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_num_cuota, mat_pago_historico.mat_descripcion, mat_id_plan, mat_pago_historico.id_concepto, mat_estado, mat_concepto.mat_concepto, mat_interes FROM mat_pago_historico, mat_concepto WHERE mat_pago_historico.id_concepto = 1 AND mat_pago_historico.mat_estado = 'A' AND mat_pago_historico.mat_matricula = ".$mat_matricula." AND mat_concepto.id_concepto = mat_pago_historico.id_concepto AND mat_pago_historico.mat_fecha_vencimiento AND  year(`mat_fecha_vencimiento`) <= year(curdate()) AND month(`mat_fecha_vencimiento`) <= month(curdate())   ORDER BY mat_pago_historico.mat_fecha_vencimiento DESC LIMIT 1)
    UNION
    (SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_num_cuota, mat_pago_historico.mat_descripcion, mat_id_plan, mat_pago_historico.id_concepto, mat_estado, mat_concepto.mat_concepto, mat_interes FROM mat_pago_historico, mat_concepto WHERE mat_pago_historico.id_concepto = 1 AND mat_pago_historico.mat_estado = 'A' AND mat_pago_historico.mat_matricula = ".$mat_matricula." AND mat_concepto.id_concepto = mat_pago_historico.id_concepto AND mat_pago_historico.mat_fecha_vencimiento AND  year(`mat_fecha_vencimiento`) <= year(curdate()) AND month(`mat_fecha_vencimiento`) <= month(curdate())  ORDER BY mat_pago_historico.mat_fecha_vencimiento ASC LIMIT 2)
    UNION
    (SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_num_cuota, mat_pago_historico.mat_descripcion, mat_id_plan, mat_pago_historico.id_concepto, mat_estado, mat_concepto.mat_concepto, mat_interes FROM mat_pago_historico, mat_concepto WHERE mat_pago_historico.id_concepto = 2 AND mat_pago_historico.mat_estado = 'A' AND mat_pago_historico.mat_matricula = ".$mat_matricula." AND mat_concepto.id_concepto = mat_pago_historico.id_concepto AND mat_pago_historico.mat_fecha_vencimiento AND  year(`mat_fecha_vencimiento`) <= year(curdate()) AND month(`mat_fecha_vencimiento`) <= month(curdate())  ORDER BY mat_pago_historico.mat_fecha_vencimiento DESC LIMIT 1)
    UNION
    (SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_num_cuota, mat_pago_historico.mat_descripcion, mat_id_plan, mat_pago_historico.id_concepto, mat_estado, mat_concepto.mat_concepto, mat_interes FROM mat_pago_historico, mat_concepto WHERE mat_pago_historico.id_concepto = 2 AND mat_pago_historico.mat_estado = 'A' AND mat_pago_historico.mat_matricula = ".$mat_matricula." AND mat_concepto.id_concepto = mat_pago_historico.id_concepto AND mat_pago_historico.mat_fecha_vencimiento AND  year(`mat_fecha_vencimiento`) <= year(curdate()) AND month(`mat_fecha_vencimiento`) <= month(curdate())   ORDER BY mat_pago_historico.mat_fecha_vencimiento ASC LIMIT 2)
    UNION
    (SELECT id_pago_historico, mat_matricula, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_num_cuota, mat_pago_historico.mat_descripcion, mat_id_plan, mat_pago_historico.id_concepto, mat_estado, mat_concepto.mat_concepto, mat_interes FROM mat_pago_historico, mat_concepto WHERE mat_pago_historico.id_concepto != 2 AND mat_pago_historico.id_concepto != 1 AND mat_pago_historico.mat_estado = 'A' AND mat_pago_historico.mat_matricula =".$mat_matricula." AND mat_concepto.id_concepto = mat_pago_historico.id_concepto AND mat_pago_historico.mat_fecha_vencimiento AND  year(`mat_fecha_vencimiento`) <= year(curdate()) AND month(`mat_fecha_vencimiento`) <= month(curdate())   ORDER BY mat_pago_historico.mat_fecha_vencimiento DESC LIMIT 1)
    "));     
             
    return $res;
  }
  
  
  private function obtenerConcepto(){
  
    $res = DB::select( DB::raw("SELECT id_concepto, mat_concepto, mat_monto, mat_interes, mat_descripcion FROM mat_concepto WHERE mat_monto !=0
    "));
    return $res;
  }


/* -------------------------------------------------------------------------- */
/*             FIN OBTENGO LOS DATOS PRIMARIOS : MATRICULAS, CONCEPTOS        */
/* -------------------------------------------------------------------------- */




/* -------------------------------------------------------------------------- */
/*            FUNCION QUE LLAMA A LAS OTRAS FUNCIONES PARA LIQUIDAR           */
/* -------------------------------------------------------------------------- */



/* -------------------------------------------------------------------------- */
/*            FIN FUNCION QUE LLAMA A LAS OTRAS FUNCIONES PARA LIQUIDAR       */
/* -------------------------------------------------------------------------- */



/* -------------------------------------------------------------------------- */
/*          FUNCIONES QUE REALIZAN CALCULOS : DEDUCCIONES, DESCUENTOS         */
/* -------------------------------------------------------------------------- */

private function calcularPercepciones($_liquidacionDetalle) {
  
  $_saldo_restante = $_liquidacionDetalle->os_liq_bruto;
  // valido si facturo mas de 1500 o el valor en concepto de ingreso brutos
      if($_saldo_restante >=  $this->TOTAL_os_ing_brutos_limite) {
        
        $this->TOTAL_os_ing_brutos = ($_saldo_restante * $this->os_ing_brutos); 
        $_saldo_restante = $_saldo_restante - ($_saldo_restante * $this->os_ing_brutos);
        if ($this->tieneSaldo($_saldo_restante,$this->os_lote_hogar)) {
          $this->TOTAL_os_lote_hogar = ($this->TOTAL_os_ing_brutos * $this->os_lote_hogar); 
          $_saldo_restante = $_saldo_restante - ($_saldo_restante * $this->os_lote_hogar);
        }
      }

      if ($this->tieneSaldo($_saldo_restante, ($_saldo_restante * $this->os_gasto_admin))) {
        $this->TOTAL_os_gasto_admin = ($_saldo_restante * $this->os_gasto_admin); 
        echo 'ga'. round($this->TOTAL_os_gasto_admin,2,PHP_ROUND_HALF_UP). ' ';
        $_saldo_restante = $_saldo_restante - ($_saldo_restante * $this->os_gasto_admin);
      }

      if ($this->tieneSaldo($_saldo_restante,$this->os_imp_cheque)) {
      $this->TOTAL_os_imp_cheque = ($_saldo_restante * $this->os_imp_cheque); 
      $_saldo_restante = $_saldo_restante - ($_saldo_restante * $this->os_imp_cheque);
      }
  // echo 'ing_brutos'. round($this->TOTAL_os_ing_brutos,2,PHP_ROUND_HALF_UP). ' ';
  // echo 'lh'. round($this->TOTAL_os_lote_hogar,2,PHP_ROUND_HALF_UP). ' ';
  // 
  // echo 'ch'. round($this->TOTAL_os_imp_cheque,2,PHP_ROUND_HALF_UP). ' ';
   $_TOTAL = round($this->TOTAL_os_ing_brutos,2,PHP_ROUND_HALF_UP) + round($this->TOTAL_os_lote_hogar,2,PHP_ROUND_HALF_UP) + round($this->TOTAL_os_gasto_admin,2,PHP_ROUND_HALF_UP) + round($this->TOTAL_os_imp_cheque,2,PHP_ROUND_HALF_UP);

  // echo round($_TOTAL,2,PHP_ROUND_HALF_UP) ;
  return $_TOTAL;
}


private function calcularDeudaMatricula(){

  $_id_pago_historico = '';
 // echo 'saldo '.round($this->saldo,2,PHP_ROUND_HALF_UP).' '  ;
  $_saldo_restante = $this->saldo;

  foreach ($this->deuda as $index => $_deuda) {

   if ($this->tieneSaldo($_saldo_restante,$_deuda->mat_monto)) {
     
     if($_deuda->id_concepto === 1) {
        //CALCULO LOS INTERESES SI ESTA VENCIDA       
        if($this->diferenciaFecha($_deuda->mat_fecha_vencimiento, date('Y-m-d')) <3){
          $this->TOTAL_os_desc_matricula =  $this->TOTAL_os_desc_matricula + $_deuda->mat_monto;
        } else {
          $this->TOTAL_os_desc_matricula =  $this->TOTAL_os_desc_matricula + ($_deuda->mat_monto * $_deuda->mat_interes);

          //  SI SE EXCEDE LOS MESES PROCEDO A CREAR UN ARREGLO DONDE SE GUARDAN LOS RENGLONES QUE SE TIENEN QUE ACTUALIZAR
          $this->interes = $_deuda->mat_interes;
          
          if(strlen($this->INTERESES_pago_historico_id) === 0){ 
            $this->INTERESES_pago_historico_id =  $this->INTERESES_pago_historico_id.   $_deuda->id_pago_historico;
          } else {
            $this->INTERESES_pago_historico_id = $this->INTERESES_pago_historico_id.  ',' .$_deuda->id_pago_historico;
          }
        }
     }
     if($_deuda->id_concepto === 2) {
      $this->TOTAL_os_desc_fondo_sol =  $this->TOTAL_os_desc_fondo_sol + $_deuda->mat_monto;
     }
     if(($_deuda->id_concepto !== 1) && ($_deuda->id_concepto !== 2)) {
      $this->TOTAL_os_otros_ing_eg = $this->TOTAL_os_otros_ing_eg + $_deuda->mat_monto;
     }
     
     $_saldo_restante = $_saldo_restante - $_deuda->mat_monto;
     
    if($_id_pago_historico === '') {
     $_id_pago_historico =  strval($_deuda->id_pago_historico);
    } else {
      $_id_pago_historico =$_id_pago_historico .','. strval($_deuda->id_pago_historico);
    }

  } 

  }
  

  echo 'IDS '.$_id_pago_historico;
  echo ' - MAT '.$this->TOTAL_os_desc_matricula;
  echo ' - FS '.$this->TOTAL_os_desc_fondo_sol;
  echo ' - OTRO '.$this->TOTAL_os_otros_ing_eg;
  echo '- SALDO  RESTANTE'.$_saldo_restante;

 return $_id_pago_historico;
}

/* -------------------------------------------------------------------------- */
/*          FIN FUNCIONES QUE REALIZAN CALCULOS : DEDUCCIONES, DESCUENTOS     */
/* -------------------------------------------------------------------------- */



/* -------------------------------------------------------------------------- */
/*            FUNCIONES VALIDADORAS : TIENE SALDO, LISTADO DE DEUDA           */
/* -------------------------------------------------------------------------- */

private function tieneSaldo($saldo,$importe) {

  $_saldo = $saldo - $importe;
  if($_saldo>= 0){    
    return true;
  } else {
    return false;
  }
}

private function diferenciaFecha($fecha, $fechaActual){
  $_fechaActual = date('Y-m-d');

  $ts1 = strtotime($fecha);
  $ts2 = strtotime($_fechaActual);

  $year1 = date('Y', $ts1);
  $year2 = date('Y', $ts2);

  $month1 = date('m', $ts1);
  $month2 = date('m', $ts2);

  $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
  echo 'DIFERENCIA FECHA '. $diff;
  return $diff; 
}


private function actualizarDeudaLiquidacion($id_pago_historico, $id_liquidacion_detalle, $mat_matricula ) {

//ACTUALIZO LOS RENGLONES COMO PAGADOS Y CON  FECHA DE PAGO
 $fecha = date('Y-m-d');
  $res = DB::update( DB::raw("
  UPDATE mat_pago_historico SET mat_estado = 'P',  mat_fecha_pago = '". $fecha."', id_usuario = 1 , id_liquidacion_detalle = ".$id_liquidacion_detalle." WHERE id_pago_historico IN  (".$id_pago_historico.") "));      

}



private function actualizarDeudaLiquidacionInteres($_id_pago_historico ) {
  //ACTUALIZO LOS RENGLONES COMO PAGADOS Y CON  FECHA DE PAGO
   $fecha = date('Y-m-d');
    $res = DB::update( DB::raw("
    UPDATE mat_pago_historico SET   mat_monto_cobrado = mat_monto , mat_monto = (mat_monto * ".$this->os_int_mora.") WHERE id_pago_historico IN  (".$_id_pago_historico.") "));      
}


  private function actualizarLiquidacionDetalleConceptos( $id_liquidacion_detalle ) {
     $this->TOTAL_os_descuentos = $this->TOTAL_os_desc_matricula + $this->TOTAL_os_desc_fondo_sol + $this->TOTAL_os_otros_ing_eg;// valores cargados a mano para realizar correcciones , debitos  o creditos
    
    $this->saldo = $this->saldo - $this->TOTAL_os_desc_matricula - $this->TOTAL_os_desc_fondo_sol - $this->TOTAL_os_otros_ing_eg;

      $res = DB::update( DB::raw("
      UPDATE os_liq_liquidacion_detalle SET  os_ing_brutos = ".$this->TOTAL_os_ing_brutos.",
      os_lote_hogar = ".$this->TOTAL_os_lote_hogar.", os_gasto_admin = ".$this->TOTAL_os_gasto_admin.", os_imp_cheque = ".$this->TOTAL_os_imp_cheque.",
      os_descuentos = ".$this->TOTAL_os_descuentos.", os_desc_matricula = ".$this->TOTAL_os_desc_matricula.",
      os_desc_fondo_sol = ".$this->TOTAL_os_desc_fondo_sol.",  os_otros_ing_eg = ".$this->TOTAL_os_otros_ing_eg.", 
      os_liq_neto = ".$this->saldo." 
         WHERE id_liquidacion_detalle  = (".$id_liquidacion_detalle.") "));      
    
    }

  
  public function recalcularPagoHistorico(Request $request ) {
    //ACTUALIZO LOS RENGLONES COMO PAGADOS Y CON  FECHA DE PAGO
    $id_liquidacion_generada = $request->input('id_liquidacion_generada');
     $fecha = date('Y-m-d');
      $res = DB::update( DB::raw("
      UPDATE mat_pago_historico SET  mat_monto =  mat_monto_cobrado, mat_estado = 'A' , id_liquidacion_detalle = 0, id_liquidacion_generada = 0, mat_fecha_pago = '0000-00-00' WHERE id_liquidacion_generada IN  (".$id_liquidacion_generada.") "));      
  }


/* -------------------------------------------------------------------------- */
/*           FIN FUNCIONES VALIDADORAS : TIENE SALDO, LISTADO DE DEUDA        */
/* -------------------------------------------------------------------------- */



public function getOrdenByMatriculaAndLiquidacion(Request $request)
{      
  $mat_matricula = $request->input('mat_matricula');
  $estado = $request->input('id_liquidacion');

  $res = DB::select( DB::raw("SELECT id_os_liq_orden, os_liq_orden.mat_matricula, os_liq_orden.id_obra_social, os_liq_orden.id_sesion, os_liq_orden.id_paciente, os_liq_orden.os_fecha, 
  os_liq_orden.os_cantidad, os_liq_orden.os_precio_sesion, os_liq_orden.os_precio_total, os_liq_orden.os_estado_liquidacion, os_liq_orden.os_liq_numero, 
  os_obra_social.os_nombre, os_sesion.id_sesion_tipo, os_sesion.id_precio, os_liq_liquidacion.os_liq_numero, 
  os_liq_liquidacion.os_fecha_desde, os_liq_liquidacion.os_fecha_hasta, os_liq_liquidacion.id_os_liquidacion, pac_paciente.pac_nombre, pac_paciente.pac_dni, pac_paciente.pac_sexo 
  FROM  os_liq_orden, os_obra_social, os_sesion, os_liq_liquidacion, pac_paciente 
  WHERE   os_liq_orden.id_obra_social = os_obra_social.id 
  AND os_liq_orden.id_sesion = os_sesion.id_sesion   
  AND os_liq_liquidacion.id_os_liquidacion = os_liq_orden.os_liq_numero 
  AND pac_paciente.id_paciente = os_liq_orden.id_paciente 
  AND os_liq_liquidacion.id_liquidacion = :id_liquidacion 
  AND os_liq_orden.mat_matricula = :mat_matricula
  "),array('mat_matricula' => $mat_matricula,
             'id_liquidacion ' => $id_liquidacion ));
    
      return response()->json($res, "200");
}


    
  public function getLiquidacionByMatriculaAndEstado(Request $request)
  {      
    $mat_matricula = $request->input('mat_matricula');
    $estado = $request->input('estado');

    $res = DB::select( DB::raw("SELECT id_os_liq_orden, mat_matricula,CONCAT(mat_matricula.mat_apellido, ' ',mat_matricula.mat_nombre ) AS mat_apellido_nombre, os_liq_orden.id_sesion,  
   os_fecha, os_cantidad, os_precio_sesion, os_precio_total, os_estado_liquidacion, os_liq_numero, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo, pac_paciente.id_paciente,
   pac_paciente.pac_nombre, pac_paciente.pac_dni, pac_paciente.pac_dni,  os_obra_social.os_nombre
   FROM os_liq_orden, mat_matricula, os_obra_social, os_sesion, os_sesion_tipo, pac_paciente 
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
   pac_paciente.pac_nombre, pac_paciente.pac_dni, pac_paciente.pac_dni, os_obra_social.os_nombre
   FROM os_liq_orden, mat_matricula, os_obra_social, os_sesion, os_sesion_tipo, pac_paciente 
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



  public function generarLiquidacionDetalle(Request $request) {   
   
    $id_liquidacion =  $request->input('id_liquidacion');

    $res = DB::select( DB::raw("SELECT os_liq_orden.mat_matricula, sum(os_precio_total) AS os_liq_bruto 
    FROM os_liq_orden, os_liq_liquidacion 
    WHERE  os_liq_orden.os_liq_numero = os_liq_liquidacion.id_os_liquidacion AND os_liq_liquidacion.id_liquidacion = :id_liquidacion GROUP BY os_liq_orden.mat_matricula
     "), array(
         'id_liquidacion' =>$id_liquidacion
       ));
       
       $i = 0;
    foreach ($this->res as $index => $_liquidacionOrden) {
      $id =    DB::table('os_liq_liquidacion_detalle')->insertGetId([
      
        'mat_matricula' => $_liquidacionOrden->mat_matricula, 
        'os_liq_bruto' => $_liquidacionOrden->os_liq_bruto,    
        'os_liq_bruto' => 0,
        'os_ing_brutos' => 0,
        'os_lote_hogar' => 0,
        'os_gasto_admin' => 0,
        'os_imp_cheque' => 0,
        'os_descuentos' => 0,
        'os_desc_matricula' => 0,
        'os_desc_fondo_sol' => 0,
        'os_otros_ing_eg' => 0,
        'os_liq_neto' => 0,
        'num_comprobante' => 0,
        'os_num_ing_bruto' => 0,
        'id_liquidacion_generada' => $id_liquidacion             
    ]);    

    $i++;
  }
  return response()->json('Se insertaron '.$i, "200");
}


public function setGenerarExpediente(Request $request){
       
  $tmp_fecha = str_replace('/', '-', $request->input('os_fecha_desde'));
  $os_fecha_desde =  date('Y-m-d', strtotime($tmp_fecha)); 
  $tmp_fecha = str_replace('/', '-', $request->input('os_fecha_hasta'));
  $os_fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha));

  $id =    DB::table('os_liq_liquidacion')->insertGetId([ 
    'id_os_obra_social' => $request->id_os_obra_social, 
    'os_liq_numero' => $request->os_liq_numero,
    'os_fecha_desde' => $os_fecha_desde,    
    'os_fecha_hasta' => $os_fecha_hasta,
    'os_cant_ordenes' => $request->os_cant_ordenes,
    'os_monto_total' => $request->os_monto_total,
    'os_estado' => $request->os_estado,
    'id_liquidacion' => 0
  ]);    
return response()->json($res, "200");
}



public function putGenerarExpediente(Request $request, $id){
       
  $tmp_fecha = str_replace('/', '-', $request->input('os_fecha_desde'));
  $os_fecha_desde =  date('Y-m-d', strtotime($tmp_fecha)); 
  $tmp_fecha = str_replace('/', '-', $request->input('os_fecha_hasta'));
  $os_fecha_hasta =  date('Y-m-d', strtotime($tmp_fecha)); 

  $res =  DB::table('os_liq_liquidacion')
  ->where('os_liq_liquidacion', $id)
  ->update([   
      'id_os_obra_social' => $request->input('id_os_obra_social'), 
      'os_liq_numero' => $request->input('os_liq_numero'),
      'os_fecha_desde' => $os_fecha_desde,    
      'os_fecha_hasta' => $os_fecha_hasta,
      'os_cant_ordenes' => $request->input('os_cant_ordenes'),
      'os_monto_total' => $request->input('os_monto_total'),
      'os_estado' => $request->input('os_estado'),
      'id_liquidacion' => $request->input('id_liquidacion')
  ]);    
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
          'mat_monto' => $request->mat_monto,    
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
