<?php

namespace App\Http\Controllers\Files;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Filesystem;
use DateTime;

use File;

class FilesController extends ApiController
{
    public function createTestTextFile(Request $request){

      $in = "";
      $i=0;
      while(isset($request[$i])){
          if($i==0){
          $in = $request[$i]["id"];
          }else{
              $in = $in.",".$request[$i]["id"];
          }
          $i++;
      }

      $horario = DB::select( DB::raw("SELECT liq_liquidacion.id as liq_liquidacion_id, liq_liquidacion.obra_social_id, liq_liquidacion.numero, nivel, fecha_desde, fecha_hasta, liquidacion_generada_id, cant_orden, total, usuario_audito, liq_liquidacion.estado as liq_liquidacion_estado, 
      operacion_cobro_practica.id, operacion_cobro_practica.valor_facturado, operacion_cobro_practica.paciente_id, operacion_cobro_practica.user_medico_id, operacion_cobro_practica.convenio_os_pmo_id, obra_social.nombre as obra_social_nombre,
      pmo.codigo, pmo.descripcion, pmo.complejidad, operacion_cobro.fecha_cobro, CONCAT(paciente.apellido,', ', paciente.nombre) AS paciente_nombre , paciente.dni as paciente_dni, users_practica.nombreyapellido as medico_nombre, medicos.codigo_old as matricula,
      paciente.barra_afiliado  as paciente_barra_afiliado, paciente.gravado_adherente, operacion_cobro.numero_bono, entidad.nombre as entidad_nombre, operacion_cobro_practica.cantidad, entidad.nombre as entidad_nombre, medicos.fecha_matricula
      FROM operacion_cobro ,liq_liquidacion, operacion_cobro_practica, obra_social,convenio_os_pmo, pmo, users, users as users_practica, paciente, entidad , medicos
      WHERE liq_liquidacion.id = operacion_cobro_practica.liquidacion_numero AND operacion_cobro_practica.convenio_os_pmo_id = convenio_os_pmo.id AND
       convenio_os_pmo.obra_social_id = obra_social.id AND convenio_os_pmo.pmo_id = pmo.id AND operacion_cobro_practica.user_medico_id = users.id AND
       operacion_cobro.id = operacion_cobro_practica.operacion_cobro_id and operacion_cobro_practica.paciente_id = paciente.id AND obra_social.entidad_factura_id = entidad.id AND
       operacion_cobro_practica.user_medico_id = users_practica.id AND users.id = medicos.usuario_id AND obra_social.entidad_factura_id = entidad.id AND liq_liquidacion.id  IN (".$in.") ORDER BY  nivel ASC,matricula ASC, operacion_cobro.fecha_cobro ASC")       );

 



         /****** BUCLE PARA GENERAR EL ARCHIVO CARGA DE DATOS ***** */



     $data = "";
    foreach ($horario as $res) {
    $tmp_fecha = str_replace('/', '-', $res->numero);
    $periodo  =  date('Ym', strtotime($tmp_fecha)); 
    $valor_facturado = 0;
    
    $matricula = str_pad($res->matricula, 4); // completo con espacios segun requerido
    $medico_nombre = str_pad($res->medico_nombre, 50); 
    $nivel = substr($res->nivel, 1, 2);// obtengo el segundo caracter para ver si  es Facturacion Refacturacion etc
    $nivel_numero = substr($res->nivel, 0, 1);
    if($res->complejidad == "1"){
      $prestacion = "C";
      $expediente = "5";
      $valor_facturado =  str_pad($res->valor_facturado,11,' ',STR_PAD_LEFT  ); 
    }
    if($res->complejidad == "2"){
      $prestacion = "A";
      $expediente = "1";
      /****** calculo el 100% de la practica ********/
      $temp_total = $res->valor_facturado;
      $temp_total = $temp_total+(($temp_total * 20)/80);
   //   echo "100% ".$temp_total;
      $valor_facturado =  str_pad($temp_total,11,' ',STR_PAD_LEFT  ); 
    }
    if($res->complejidad == "3"){
      $prestacion = "I";
      $expediente = "7";
    }

    $tipo_practica = $res->complejidad;
    $_paciente_dni = str_pad($res->paciente_dni, 8,'0',STR_PAD_LEFT);
    $paciente_dni = str_pad($_paciente_dni,25 ); 
    $paciente_nombre = str_pad($res->paciente_nombre,35); 
    $paciente_barra_afiliado = str_pad($res->paciente_barra_afiliado, 3,'0');
    $codigo = str_replace('.', '', $res->codigo);
    //$cantidad = substr($res->cantidad, 0, 1);    
    $cantidad = round($res->cantidad, 0, PHP_ROUND_HALF_UP) ; // REDONDEO A LA CANTIDAD SI ES DECIMAL 1.5 REDONDEA 2
    
    $valor_facturado_vacio = str_pad("0.00",11,' ',STR_PAD_LEFT  );
    $valor_reconoce_dos = str_pad($res->valor_facturado,11,' ',STR_PAD_LEFT  );
    /*** CATEGORIA DEL MEDICO */
    //$categoria_anio = $this->yearsMonthsBetween($tmp_fecha,$res->fecha_matricula);
    $d1 = new DateTime($periodo); // FECHA ACTUAL
    $d2 = new DateTime($res->fecha_matricula); // FECHA MATRICULA
    $diff = $d2->diff($d1);    
    $categoria_anio =$diff->y;
    //echo $categoria_anio;
    // CALCULO LA CATEGORIA DE ACUERDO A LA ANTIGUEDAD DEL MEDICO
     if($categoria_anio<=9){
       $categoria = "A";
     }
     if(($categoria_anio>=10)&&($categoria_anio<=19)){
      $categoria = "B";
     }
     if($categoria_anio>=20){
      $categoria = "C";
     }
     $temp_bono =  str_pad($res->numero_bono,10, '0',STR_PAD_LEFT ); // completar en caso que olviden rellenar con ceros
     $numero_bono = str_pad($temp_bono,15, ' ',STR_PAD_RIGHT ); 
    $fechaIngresoInternacion  =  date('YmdHi', strtotime($res->numero));  // no utilizado en nivel 1 y 2
    $fechaRealiacionPratica = date('YmdHi', strtotime($res->fecha_cobro)); 
    $gravado_adherente = $res->gravado_adherente;


    $cargarRegistro = 'A'.";". $periodo.";". ''.";". $matricula .";".$medico_nombre.";". $nivel.";". $prestacion.";". $tipo_practica .";". 
    $paciente_dni.";". $paciente_nombre.";". $paciente_barra_afiliado.";". $codigo.";".$cantidad.";".$valor_facturado.";". $valor_facturado_vacio.";". $valor_reconoce_dos.";".
    $valor_facturado_vacio.";".$valor_facturado_vacio.";". $categoria.";". '0'.";". $numero_bono.";".''.";". $fechaRealiacionPratica.";". '1'.";". $expediente.";".
    $gravado_adherente.";". ''.";". ''.";". ''.";". ''.";". ''.";". ''.";". ''.";". ''."\n";
    $data = $data.$cargarRegistro;
  
  }

    /******************************************************************** */
      /****** CREACION DEL ARCHIVO ***** */

      $destinationPath=public_path()."/TXT/dos/";  
      $file = 'A_'.$periodo.'_DR_'.$matricula.'_'.$nivel_numero.'.txt';
    
      if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
      File::put($destinationPath.$file,$data);
     // return response()->download($destinationPath.$file);
     return response()->json($horario, 201);    
    }


    function yearsMonthsBetween ( $date1, $date2 ) {
	
      $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    
    $interval = date_diff($datetime1, $datetime2);
    
    return $interval->format($differenceFormat);
     return $diff->y;
    }

}
