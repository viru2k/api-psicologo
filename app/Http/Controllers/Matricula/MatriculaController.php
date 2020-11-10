<?php

namespace App\Http\Controllers\Matricula;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Auth\AuthenticationException; 
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Hash;

class MatriculaController extends ApiController
{
    
  public function getMatriculas()
  {      
    $res = DB::select( DB::raw("SELECT id, mat_matricula_psicologo_nacional, mat_matricula_psicologo, mat_apellido, mat_nombre, mat_sexo, mat_localidad, mat_domicilio_particular, mat_domicilio_laboral, mat_tel_particular, mat_tel_laboral, mat_lugar_laboral, mat_email, mat_tipo_dni, mat_dni, mat_num_cuenta, mat_fecha_nacimiento, mat_fecha_egreso, mat_fecha_matricula, mat_estado_matricula, mat_especialidad, mat_orientacion, mat_abordaje, mat_excento, mat_cuit, mat_ning_bto, mat_banco, mat_cbu, mat_nro_folio, mat_nro_acta, mat_fallecido, mat_historial, mat_numero_superintendecia, 
    mat_n_superintendencia_fecha_vencimiento FROM mat_matricula WHERE 1
    "));
        return response()->json($res, "200");
  }


  public function getMatricula(Request $request)
  {      
    $matricula_id =  $request->input('matricula_id');  
    $res = DB::select( DB::raw("SELECT id, mat_matricula_psicologo_nacional, mat_matricula_psicologo, mat_apellido, mat_nombre, mat_sexo, mat_localidad, mat_domicilio_particular, mat_domicilio_laboral, mat_tel_particular, mat_tel_laboral, 
    mat_lugar_laboral, mat_email, mat_tipo_dni, mat_dni, mat_num_cuenta, mat_fecha_nacimiento, mat_fecha_egreso, mat_fecha_matricula, mat_estado_matricula, 
    mat_especialidad, mat_orientacion, mat_abordaje, mat_excento, mat_cuit, mat_ning_bto, mat_banco, mat_cbu, mat_nro_folio, mat_nro_acta, mat_fallecido, mat_historial, 
    mat_numero_superintendecia, mat_n_superintendencia_fecha_vencimiento
    FROM mat_matricula WHERE  mat_matricula.mat_matricula_psicologo = :matricula_id
    "),
     array(                       
      'matricula_id' => $matricula_id
    ));
        return response()->json($res, "200");
  }


  public function getMatriculaObraSocial(Request $request)
  {      
    $matricula_id =  $request->input('matricula_id');  

    $res = DB::select( DB::raw("SELECT mat_matricula.id, mat_matricula_psicologo_nacional, mat_matricula_psicologo, mat_apellido, mat_nombre, mat_sexo,mat_matricula.mat_tipo_dni,  mat_matricula.mat_dni, mat_matricula_obra_social.id as mat_matricula_obra_social_id, mat_matricula_obra_social.obra_social_id,  obra_social.nombre  as obra_social_nombre
    FROM mat_matricula, mat_matricula_obra_social, obra_social 
    WHERE mat_matricula.id = mat_matricula_obra_social.matricula_id AND obra_social.id = mat_matricula_obra_social.obra_social_id AND mat_matricula.mat_matricula_psicologo = :matricula_id
    "),
     array(                       
      'matricula_id' => $matricula_id
    ));
        return response()->json($res, "200");
  }


  public function setMatricula(Request $request) {

    $tmp_fecha = str_replace('/', '-', $request->mat_fecha_nacimiento);
    $mat_fecha_nacimiento =  date('Y-m-d', strtotime($tmp_fecha));  

    $tmp_fecha = str_replace('/', '-', $request->mat_fecha_egreso);
    $mat_fecha_egreso =  date('Y-m-d', strtotime($tmp_fecha)); 

    $tmp_fecha = str_replace('/', '-', $request->mat_fecha_matricula);
    $mat_fecha_matricula =  date('Y-m-d', strtotime($tmp_fecha));   

    $tmp_fecha = str_replace('/', '-', $request->mat_n_superintendencia_fecha_vencimiento);
    $mat_n_superintendencia_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha)); 

    $id =    DB::table('mat_matricula')->insertGetId([
      
      'mat_matricula_psicologo_nacional' => $request->mat_matricula_psicologo_nacional, 
      'mat_matricula_psicologo' => $request->mat_matricula_psicologo,    
      'mat_apellido' => $request->mat_apellido,    
      'mat_nombre' => $request->mat_nombre,    
      'mat_sexo' => $request->mat_sexo,    
      'mat_localidad' => $request->mat_localidad,    
      'mat_domicilio_particular' => $request->mat_domicilio_particular,    
      'mat_domicilio_laboral' => $request->mat_domicilio_laboral,    
      'mat_tel_particular' => $request->mat_tel_particular,    
      'mat_tel_laboral' => $request->mat_tel_laboral,    
      'mat_lugar_laboral' => $request->mat_lugar_laboral,    
      'mat_email' => $request->mat_email,    
      'mat_tipo_dni' => $request->mat_tipo_dni,    
      'mat_dni' => $request->mat_dni,    
      'mat_num_cuenta' => $request->mat_num_cuenta,    
      'mat_fecha_nacimiento' => $mat_fecha_nacimiento,    
      'mat_fecha_egreso' => $mat_fecha_egreso,    
      'mat_fecha_matricula' => $mat_fecha_matricula,    
      'mat_estado_matricula' => $request->mat_estado_matricula,    
      'mat_especialidad' => $request->mat_especialidad,    
      'mat_orientacion' => $request->mat_orientacion,    
      'mat_abordaje' => $request->mat_abordaje,    
      'mat_excento' => $request->mat_excento,    
      'mat_cuit' => $request->mat_cuit,    
      'mat_ning_bto' => $request->mat_ning_bto,    
      'mat_banco' => $request->mat_banco, 
      'mat_cbu' => $request->mat_cbu, 
      'mat_nro_folio' => $request->mat_nro_folio, 
      'mat_nro_acta' => $request->mat_nro_acta,       
      'mat_fallecido' => $request->mat_fallecido, 
      'mat_historial' => $request->mat_historial, 
      'mat_numero_superintendecia' => $request->mat_numero_superintendecia, 
      'mat_n_superintendencia_fecha_vencimiento' => $mat_n_superintendencia_fecha_vencimiento
  ]);    
    return response()->json($id, "200");  
  }

  
  public function putMatricula(Request $request, $id){

    $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_nacimiento'));
    $mat_fecha_nacimiento =  date('Y-m-d', strtotime($tmp_fecha));   
    $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_egreso'));
    $mat_fecha_egreso =  date('Y-m-d', strtotime($tmp_fecha));   
    $tmp_fecha = str_replace('/', '-', $request->input('mat_fecha_matricula'));
    $mat_fecha_matricula =  date('Y-m-d', strtotime($tmp_fecha));   
    $tmp_fecha = str_replace('/', '-', $request->input('mat_n_superintendencia_fecha_vencimiento'));
    $mat_n_superintendencia_fecha_vencimiento =  date('Y-m-d', strtotime($tmp_fecha));  


    $res =  DB::table('mat_matricula')
    ->where('id', $id)
    ->update([
      'mat_matricula_psicologo_nacional' => $request->input('mat_matricula_psicologo_nacional'),
      'mat_matricula_psicologo' => $request->input('mat_matricula_psicologo'),
      'mat_apellido' => $request->input('mat_apellido'),
      'mat_nombre' => $request->input('mat_nombre'),
      'mat_sexo' => $request->input('mat_sexo'),
      'mat_localidad' => $request->input('mat_localidad'),
      'mat_domicilio_particular' => $request->input('mat_domicilio_particular'),
      'mat_domicilio_laboral' => $request->input('mat_domicilio_laboral'),
      'mat_tel_particular' => $request->input('mat_tel_particular'),
      'mat_tel_laboral' => $request->input('mat_tel_laboral'),
      'mat_lugar_laboral' => $request->input('mat_lugar_laboral'),
      'mat_email' => $request->input('mat_email'),
      'mat_tipo_dni' => $request->input('mat_tipo_dni'),
      'mat_dni' => $request->input('mat_dni'),
      'mat_num_cuenta' => $request->input('mat_num_cuenta'),
      'mat_fecha_nacimiento' => $mat_fecha_nacimiento,
      'mat_fecha_egreso' => $mat_fecha_egreso,
      'mat_fecha_matricula' => $mat_fecha_matricula,
      'mat_estado_matricula' => $request->input('mat_estado_matricula'),
      'mat_especialidad' => $request->input('mat_especialidad'),
      'mat_orientacion' => $request->input('mat_orientacion'),
      'mat_abordaje' => $request->input('mat_abordaje'),
      'mat_excento' => $request->input('mat_excento'),
      'mat_cuit' => $request->input('mat_cuit'),
      'mat_ning_bto' => $request->input('mat_ning_bto'),
      'mat_banco' => $request->input('mat_banco'),
      'mat_cbu' => $request->input('mat_cbu'),
      'mat_nro_folio' => $request->input('mat_nro_folio'),
      'mat_nro_acta' => $request->input('mat_nro_acta'),      
      'mat_fallecido' => $request->input('mat_fallecido'),
      'mat_historial' => $request->input('mat_historial'),
      'mat_numero_superintendecia' => $request->input('mat_numero_superintendecia'),      
      'mat_n_superintendencia_fecha_vencimiento' => $mat_n_superintendencia_fecha_vencimiento
      ]);
      
      return response()->json($res, "200");
  }


  public function setMatriculaObraSocial(Request $request,$id){       
      
    $i = 0;
    $_request = $request->request->all();
 //  echo $request[0]['modulo_id'];
   foreach($_request as $req) {
    $res = DB::select( DB::raw(" INSERT INTO mat_matricula_obra_social (matricula_id, obra_social_id) SELECT '".$id."','".$req['id']."' FROM DUAL
    WHERE NOT EXISTS 
      (SELECT matricula_id, obra_social_id FROM mat_matricula_obra_social WHERE matricula_id = '".$id."' AND obra_social_id= '".$req['id']."' )"));
    $i++; 
   }
  
/*     $modulo_id =   $request['modulo_id'] ;
     
     $id= DB::table('user_modulo')->insertGetId(
       [ 'user_id' => $id,
        'modulo_id' =>  $modulo_id    ]    
    );

*/
  //  var_dump($request);

    return response()->json($request, "201"); 
//echo $id;
}

  public function borrarMatriculaObraSocial($id){            
    DB::table('mat_matricula_obra_social')->where('id', $id)->delete();
    
    return response()->json("ok", "201"); 
//echo $id;
}



public function getPacienteByCondicion(Request $request)
{      
  $pac_dni =  $request->input('pac_dni');  
  $condicion =  $request->input('condicion');  

  if ( $condicion === 'dni'){
    $res = DB::select( DB::raw("SELECT `id_paciente`, `pac_nombre`, `pac_sexo`, `pac_dni`, `pac_diagnostico` FROM `pac_paciente` WHERE  pac_dni LIKE '%".$pac_dni."%'
    "));
  }
  if ( $condicion === 'apellido'){
    $res = DB::select( DB::raw("SELECT `id_paciente`, `pac_nombre`, `pac_sexo`, `pac_dni`, `pac_diagnostico` FROM `pac_paciente` WHERE  pac_nombre LIKE '%".$pac_dni."%'
    "));
  }

      return response()->json($res, "200");
}
  
}
