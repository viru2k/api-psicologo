<?php

namespace App\Http\Controllers\Agenda;

use App\Agenda;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 
use App\models\AgendaHorario; 
use App\models\AgendaHorarioAtencion;


class AgendaController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     * @param  \App\Agenda  $agenda
     * @return \Illuminate\Http\Response
     */


    public function getAgendaByHorarios(Request $request )
    {

        $input = $request->all();        
        $horario = DB::table('agenda_horario')->select('id', 'agenda_horaria_nro', 'hora_desde','hora_hasta','hora_desde_hasta')->get();
        return $this->showAll($horario);
    }
    public function getAgendaByMedico(Request $request )
    {

        $input = $request->all();
        $medico_id = $request->input('medico_id');
        $dia = $request->input('dia');
        $horario = DB::table('agenda_horario')->select('agenda_horaria_nro', 'hora_desde','hora_hasta','hora_desde_hasta')->get();
        return $this->showAll($horario);
    }

    public function getAgendaAtencionByFechaTodos(Request $request )
    {
           
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
        $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));             
    

               $horario = DB::select( DB::raw("
               SELECT agenda_usuario_dia_horario.id,agenda_horario.hora_desde,agenda_horario.hora_hasta,agenda_horario.hora_desde_hasta,agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id,agenda_dia_horario_atencion.fecha_turno,agenda_dia_horario_atencion.llegada,agenda_dia_horario_atencion.atendido,agenda_dia_horario_atencion.es_observacion, agenda_dia_horario_atencion.operacion_cobro_id,agenda_dia_horario_atencion.observacion,agenda_dia_horario_atencion.es_alerta,agenda_estado.id as agenda_estado_id,agenda_estado.estado,agenda_dia_id,medicos.usuario_id,users.nombreyapellido,dia_nombre,dia_nro,paciente.id as paciente_id,paciente.nombre as paciente_nombre,paciente.apellido as paciente_apellido,paciente.dni as paciente_dni,paciente.fecha_nacimiento as paciente_fecha_nacimiento,paciente.obra_social_id as paciente_obra_social_id,obra_social.nombre as paciente_obra_social_nombre,obra_social.tiene_distribucion,paciente.coseguro_id as paciente_coseguro_id,coseguro.nombre as paciente_coseguro_nombre,coseguro.es_coseguro as coseguro_es_coseguro,paciente.barra_afiliado,paciente.numero_afiliado ,
               medicos.fecha_matricula, paciente.telefono_cel as telefono_cel,
               paciente.telefono_fijo as telefono_fijo
                FROM agenda_usuario_dia_horario, agenda_dia_horario_atencion,agenda_dias, 
                agenda_horario, agenda_estado, paciente, 
                obra_social, obra_social as coseguro, users, users as user_medico, medicos
                WHERE 
                agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  = agenda_usuario_dia_horario.id AND  
                agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id AND 
                agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id AND 
                agenda_estado.id = agenda_dia_horario_atencion.agenda_estado_id AND 
                paciente.id = agenda_dia_horario_atencion.paciente_id AND 
                obra_social.id = paciente.obra_social_id AND coseguro.id = paciente.coseguro_id AND 
                users.id = agenda_usuario_dia_horario.usuario_id AND 
                user_medico.id =agenda_usuario_dia_horario.usuario_id AND 
                medicos.usuario_id =user_medico.id   AND 
                agenda_dia_horario_atencion.fecha_turno = :fecha_turno AND agenda_estado.id IN(1,2,3,5,6,7)  
            ORDER BY agenda_horario.id ASC
                "), array(
                    'fecha_turno' => $fecha_turno
                  ));

        return response()->json($horario, 201);
    
    }



// METODO QUE DEVUELVE LOS PACIENTES QUE ESTAN A LA ESPERA DE SER ATENDIDOS
    public function getAgendaAtencionByFechaTurnosTodos(Request $request )
    {
           
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
        $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));             
    

               $horario = DB::select( DB::raw("
               SELECT agenda_usuario_dia_horario.id,agenda_horario.hora_desde,agenda_horario.hora_hasta,agenda_horario.hora_desde_hasta,agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id,agenda_dia_horario_atencion.fecha_turno,agenda_dia_horario_atencion.llegada,agenda_dia_horario_atencion.atendido, agenda_dia_horario_atencion.es_observacion, agenda_dia_horario_atencion.operacion_cobro_id, agenda_dia_horario_atencion.observacion,agenda_dia_horario_atencion.es_alerta,agenda_estado.id as agenda_estado_id,agenda_estado.estado,agenda_dia_id,medicos.usuario_id,users.nombreyapellido,dia_nombre,dia_nro,paciente.id as paciente_id,paciente.nombre as paciente_nombre,paciente.apellido as paciente_apellido,paciente.dni as paciente_dni,paciente.fecha_nacimiento as paciente_fecha_nacimiento,paciente.obra_social_id as paciente_obra_social_id,obra_social.nombre as paciente_obra_social_nombre,obra_social.tiene_distribucion,paciente.coseguro_id as paciente_coseguro_id,coseguro.nombre as paciente_coseguro_nombre,coseguro.es_coseguro as coseguro_es_coseguro,paciente.barra_afiliado,paciente.numero_afiliado ,
               medicos.fecha_matricula, paciente.telefono_cel as telefono_cel,
               paciente.telefono_fijo as telefono_fijo, es_sobreturno
                FROM agenda_usuario_dia_horario, agenda_dia_horario_atencion,agenda_dias, 
                agenda_horario, agenda_estado, paciente, 
                obra_social, obra_social as coseguro, users, users as user_medico, medicos
                WHERE 
                agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  = agenda_usuario_dia_horario.id AND  
                agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id AND 
                agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id AND 
                agenda_estado.id = agenda_dia_horario_atencion.agenda_estado_id AND 
                paciente.id = agenda_dia_horario_atencion.paciente_id AND 
                obra_social.id = paciente.obra_social_id AND coseguro.id = paciente.coseguro_id AND 
                users.id = agenda_usuario_dia_horario.usuario_id AND 
                user_medico.id =agenda_usuario_dia_horario.usuario_id AND 
                medicos.usuario_id =user_medico.id   AND 
                agenda_dia_horario_atencion.fecha_turno = :fecha_turno AND agenda_estado.id IN(1,2,3,4,5,6,7)  
            ORDER BY agenda_horario.id ASC
                "), array(
                    'fecha_turno' => $fecha_turno
                  ));

        return response()->json($horario, 201);
    
    }


    
// METODO QUE DEVUELVE LOS PACIENTES POR FECHA Y MEDICO
public function getAgendaAtencionByFechaAndMedico(Request $request )
{
          
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));     
    $agenda_estado_id =  $request->input('agenda_estado_id');
    $usuario_id =  $request->input('usuario_id');
 //echo $fecha_turno;
 $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion','paciente')
 ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')           
 ->join('agenda_dia_horario_atencion', 'agenda_dia_horario_atencion.agenda_usuario_dia_horario_id', '=', 'agenda_usuario_dia_horario.id')
 ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
 ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
 ->join('agenda_estado', 'agenda_estado.id', '=', 'agenda_dia_horario_atencion.agenda_estado_id')
 ->join('paciente', 'paciente.id','=','agenda_dia_horario_atencion.paciente_id')
 ->join('obra_social', 'obra_social.id','=','paciente.obra_social_id')
 ->join('obra_social as coseguro', 'coseguro.id','=','paciente.coseguro_id')
 ->join('users as usuario_genero', 'usuario_genero.id', '=', 'agenda_dia_horario_atencion.usuario_alta_id')  
 ->select(
    'agenda_usuario_dia_horario.id',
    'agenda_horario.hora_desde',
    'agenda_horario.hora_hasta',
    'agenda_horario.hora_desde_hasta',
    'agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id',
    'agenda_dia_horario_atencion.fecha_turno',
    'agenda_dia_horario_atencion.llegada',
    'agenda_dia_horario_atencion.atendido',
    'agenda_dia_horario_atencion.es_observacion',
    'agenda_dia_horario_atencion.operacion_cobro_id',
    'agenda_dia_horario_atencion.observacion',
    'agenda_dia_horario_atencion.es_alerta',
    'agenda_dia_horario_atencion.es_sobreturno',
    'agenda_dia_horario_atencion.usuario_alta_id',
    'usuario_genero.nombreyapellido as usuario_alta',
    'agenda_estado.id as agenda_estado_id',
    'agenda_estado.estado',
    'agenda_dia_id',
    'usuario_id',
    'users.nombreyapellido',
    'dia_nombre',
    'dia_nro',
    'paciente.id as paciente_id',
    'paciente.nombre as paciente_nombre',
    'paciente.apellido as paciente_apellido',
    'paciente.dni as paciente_dni',
    'paciente.telefono_cel as telefono_cel',
    'paciente.telefono_fijo as telefono_fijo',
    'paciente.fecha_nacimiento as paciente_fecha_nacimiento',
    'paciente.obra_social_id as paciente_obra_social_id',
    'paciente.plan',
    'paciente.numero_afiliado', 
    'paciente.domicilio',
    'obra_social.nombre as paciente_obra_social_nombre',
    'obra_social.tiene_distribucion',
    'paciente.coseguro_id as paciente_coseguro_id',
    'coseguro.nombre as paciente_coseguro_nombre',
    'coseguro.es_coseguro as coseguro_es_coseguro',
    'paciente.barra_afiliado',
    'paciente.numero_afiliado')
        ->where('agenda_dia_horario_atencion.fecha_turno','=',$fecha_turno)                        
        ->where('agenda_usuario_dia_horario.usuario_id','=',$usuario_id)
        ->whereIn('agenda_estado.id', [1,2,3,5,6])
        ->orderBy('agenda_horario.id', 'asc')
        ->get();
       


/*
        $horario = DB::select( DB::raw (
        "SELECT  agenda_usuario_dia_horario.id,agenda_horario.hora_desde,agenda_horario.hora_hasta,agenda_horario.hora_desde_hasta,agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id,agenda_dia_horario_atencion.fecha_turno,agenda_dia_horario_atencion.llegada,agenda_dia_horario_atencion.atendido,agenda_dia_horario_atencion.observacion,agenda_dia_horario_atencion.es_alerta,agenda_dia_horario_atencion.usuario_alta_id,usuario_genero.nombreyapellido as usuario_alta,agenda_estado.id as agenda_estado_id,agenda_estado.estado,agenda_dia_id,usuario_id,users.nombreyapellido,dia_nombre,dia_nro,paciente.id as paciente_id,paciente.nombre as paciente_nombre,paciente.apellido as paciente_apellido,paciente.dni as paciente_dni,paciente.telefono_cel as telefono_cel,paciente.telefono_fijo as telefono_fijo,paciente.fecha_nacimiento as paciente_fecha_nacimiento,paciente.obra_social_id as paciente_obra_social_id,paciente.plan,paciente.numero_afiliado, paciente.domicilio,obra_social.nombre as paciente_obra_social_nombre,obra_social.tiene_distribucion,paciente.coseguro_id as paciente_coseguro_id,coseguro.nombre as paciente_coseguro_nombre,coseguro.es_coseguro as coseguro_es_coseguro,paciente.barra_afiliado, paciente.numero_afiliado 
        FROM agenda_usuario_dia_horario,agenda_horario, users,users as usuario_genero,  agenda_dias,agenda_dia_horario_atencion,paciente,obra_social, obra_social as coseguro, agenda_estado
        WHERE 
        users.id = agenda_usuario_dia_horario.usuario_id AND 
        usuario_genero.id = agenda_dia_horario_atencion.usuario_alta_id AND 
        agenda_dia_horario_atencion.agenda_usuario_dia_horario_id = agenda_usuario_dia_horario.id  AND 
        agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id AND 
        agenda_estado.id = agenda_dia_horario_atencion.agenda_estado_id AND 
        paciente.id = agenda_dia_horario_atencion.paciente_id  AND 
        obra_social.id = paciente.obra_social_id AND 
        coseguro.id = paciente.coseguro_id AND agenda_dia_horario_atencion.fecha_turno =:fecha_turno AND agenda_usuario_dia_horario.usuario_id =:usuario_id AND agenda_estado.id IN (1,2,3,5,6)    ORDER BY agenda_horario.id ASC
"), array(
    'fecha_turno' => $fecha_turno,
    'usuario_id' => $usuario_id,
  ));*/


  return response()->json($horario, 201);

}



public function getAgendaAtencionByFechaTodosSinEstado(Request $request )
{
       
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));         
    
    $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion','paciente')
    ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')           
    ->join('agenda_dia_horario_atencion', 'agenda_dia_horario_atencion.agenda_usuario_dia_horario_id', '=', 'agenda_usuario_dia_horario.id')
    ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
    ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
    ->join('agenda_estado', 'agenda_estado.id', '=', 'agenda_dia_horario_atencion.agenda_estado_id')
    ->join('paciente', 'paciente.id','=','agenda_dia_horario_atencion.paciente_id')
    ->join('obra_social', 'obra_social.id','=','paciente.obra_social_id')
    ->join('obra_social as coseguro', 'coseguro.id','=','paciente.coseguro_id')
    ->join('users as usuario_genero', 'usuario_genero.id', '=', 'agenda_dia_horario_atencion.usuario_alta_id')  
    ->select(
       'agenda_usuario_dia_horario.id',
       'agenda_horario.hora_desde',
       'agenda_horario.hora_hasta',
       'agenda_horario.hora_desde_hasta',
       'agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id',
       'agenda_dia_horario_atencion.fecha_turno',
       'agenda_dia_horario_atencion.llegada',
       'agenda_dia_horario_atencion.atendido',
       'agenda_dia_horario_atencion.es_observacion',
       'agenda_dia_horario_atencion.operacion_cobro_id',
       'agenda_dia_horario_atencion.observacion',
       'agenda_dia_horario_atencion.es_alerta',
       'agenda_dia_horario_atencion.es_sobreturno',
       'agenda_dia_horario_atencion.usuario_alta_id',
       'usuario_genero.nombreyapellido as usuario_alta',
       'agenda_estado.id as agenda_estado_id',
       'agenda_estado.estado',
       'agenda_dia_id',
       'usuario_id',
       'users.nombreyapellido',
       'dia_nombre',
       'dia_nro',
       'paciente.id as paciente_id',
       'paciente.nombre as paciente_nombre',
       'paciente.apellido as paciente_apellido',
       'paciente.dni as paciente_dni',
       'paciente.telefono_cel as telefono_cel',
       'paciente.telefono_fijo as telefono_fijo',
       'paciente.fecha_nacimiento as paciente_fecha_nacimiento',
       'paciente.obra_social_id as paciente_obra_social_id',
       'paciente.plan',
       'paciente.numero_afiliado', 
       'paciente.domicilio',
       'obra_social.nombre as paciente_obra_social_nombre',
       'obra_social.tiene_distribucion',
       'paciente.coseguro_id as paciente_coseguro_id',
       'coseguro.nombre as paciente_coseguro_nombre',
       'coseguro.es_coseguro as coseguro_es_coseguro',
       'paciente.barra_afiliado',
       'paciente.numero_afiliado',
       'agenda_dia_horario_atencion.created_at as agenda_creacion')
           ->where('agenda_dia_horario_atencion.fecha_turno','=',$fecha_turno)                                   

           ->orderBy('agenda_horario.id', 'asc')
           ->get();
       
    return $this->showAll($horario);

}





// METODO QUE DEVUELVE LOS PACIENTES POR FECHA Y MEDICO
public function getAgendaAtencionByFechaAndMedicoSinEstado(Request $request )
{
       
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));         
    $usuario_id =  $request->input('usuario_id');
 
    $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion','paciente')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')                
        ->join('agenda_dia_horario_atencion', 'agenda_dia_horario_atencion.agenda_usuario_dia_horario_id', '=', 'agenda_usuario_dia_horario.id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->join('agenda_estado', 'agenda_estado.id', '=', 'agenda_dia_horario_atencion.agenda_estado_id')
        ->join('paciente', 'paciente.id','=','agenda_dia_horario_atencion.paciente_id')
        ->join('obra_social', 'obra_social.id','=','paciente.obra_social_id')
        ->join('obra_social as coseguro', 'coseguro.id','=','paciente.coseguro_id')
        ->join('users as usuario_audita', 'usuario_audita.id', '=', 'agenda_dia_horario_atencion.usuario_alta_id')        
        ->select(
            'agenda_usuario_dia_horario.id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id',
            'agenda_dia_horario_atencion.fecha_turno',
            'agenda_dia_horario_atencion.llegada',
            'agenda_dia_horario_atencion.atendido',
            'agenda_dia_horario_atencion.operacion_cobro_id',
            'agenda_dia_horario_atencion.es_observacion',
            'agenda_dia_horario_atencion.observacion',
            'agenda_dia_horario_atencion.es_alerta',
            'agenda_dia_horario_atencion.es_sobreturno',
            'agenda_estado.id as agenda_estado_id',
            'agenda_estado.estado',
            'agenda_dia_id',
            'usuario_id',
            'users.nombreyapellido',
            'dia_nombre',
            'usuario_audita.nombreyapellido as usuario_alta',
            'dia_nro',
            'paciente.id as paciente_id',
            'paciente.nombre as paciente_nombre',
            'paciente.apellido as paciente_apellido',
            'paciente.dni as paciente_dni',
            'paciente.fecha_nacimiento as paciente_fecha_nacimiento',
            'paciente.obra_social_id as paciente_obra_social_id',
            'paciente.telefono_cel as telefono_cel',
            'paciente.telefono_fijo as telefono_fijo',
            'obra_social.nombre as paciente_obra_social_nombre',
            'obra_social.tiene_distribucion',
            'paciente.coseguro_id as paciente_coseguro_id',
            'coseguro.nombre as paciente_coseguro_nombre',
            'coseguro.es_coseguro as coseguro_es_coseguro',
            'paciente.barra_afiliado',
            'paciente.numero_afiliado')
        ->where('agenda_dia_horario_atencion.fecha_turno','=',$fecha_turno)                                
        ->where('agenda_usuario_dia_horario.usuario_id','=',$usuario_id)
        ->get();
       
    return $this->showAll($horario);

}





public function getAgendaAtByFechaMedicoTurnosTodos(Request $request )
{  
      
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));             


           $horario = DB::select( DB::raw("
           SELECT agenda_usuario_dia_horario.id,agenda_horario.hora_desde,agenda_horario.hora_hasta,agenda_horario.hora_desde_hasta,agenda_dia_horario_atencion.id as agenda_dia_horario_atencion_id,agenda_dia_horario_atencion.fecha_turno,agenda_dia_horario_atencion.llegada,agenda_dia_horario_atencion.atendido,agenda_dia_horario_atencion.es_observacion,agenda_dia_horario_atencion.operacion_cobro_id,agenda_dia_horario_atencion.observacion,agenda_dia_horario_atencion.es_alerta,agenda_estado.id as agenda_estado_id,agenda_estado.estado,agenda_dia_id,medicos.usuario_id,users.nombreyapellido,dia_nombre,dia_nro,paciente.id as paciente_id,paciente.nombre as paciente_nombre,paciente.apellido as paciente_apellido,paciente.dni as paciente_dni,paciente.fecha_nacimiento as paciente_fecha_nacimiento,paciente.obra_social_id as paciente_obra_social_id,obra_social.nombre as paciente_obra_social_nombre,obra_social.tiene_distribucion,paciente.coseguro_id as paciente_coseguro_id,coseguro.nombre as paciente_coseguro_nombre,coseguro.es_coseguro as coseguro_es_coseguro,paciente.barra_afiliado,paciente.numero_afiliado ,
           medicos.fecha_matricula, paciente.telefono_cel as telefono_cel,
           paciente.telefono_fijo as telefono_fijo, es_sobreturno
            FROM agenda_usuario_dia_horario, agenda_dia_horario_atencion,agenda_dias, 
            agenda_horario, agenda_estado, paciente, 
            obra_social, obra_social as coseguro, users, users as user_medico, medicos
            WHERE 
            agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  = agenda_usuario_dia_horario.id AND  
            agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id AND 
            agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id AND 
            agenda_estado.id = agenda_dia_horario_atencion.agenda_estado_id AND 
            paciente.id = agenda_dia_horario_atencion.paciente_id AND 
            obra_social.id = paciente.obra_social_id AND coseguro.id = paciente.coseguro_id AND 
            users.id = agenda_usuario_dia_horario.usuario_id AND 
            user_medico.id =agenda_usuario_dia_horario.usuario_id AND 
            medicos.usuario_id =user_medico.id   AND 
            agenda_dia_horario_atencion.fecha_turno = :fecha_turno AND agenda_estado.id IN(1,2,3,5,6,7)  
        ORDER BY agenda_horario.id ASC
            "), array(
                'fecha_turno' => $fecha_turno
              ));

    return response()->json($horario, 201);
       

}


    
// METODO QUE DEVUELVE LOS TURNOS DISPONIBLES PARA LA FECHA PARA TODOS LOS MEDICOS --- TELEFONISTAS Y TUNOS NUEVOS

public function getAgendaAtByFechaUsuarioSobreTurno(Request $request )
{
        
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));    
    $agenda_estado_id =  $request->input('agenda_estado_id');
    $usuario_id =  $request->input('usuario_id');
    $day_number = date('N', strtotime($fecha_turno));
    $day_number = $day_number;
   // $horario = DB::select( DB::raw("SELECT agenda_horario.id, hora_desde, hora_hasta, hora_desde_hasta, agenda_horaria_id, agenda_dias.id as agenda_dia_id, dia_nro, dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = :fecha_turno ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id and users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null  and dia_nro = :day_number and users.id = :usuario_id 
   $horario = DB::select( DB::raw("SELECT agenda_horario.id, hora_desde, hora_hasta, hora_desde_hasta, agenda_horaria_id, agenda_dias.id as agenda_dia_id, dia_nro, dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id and users.id = agenda_usuario_dia_horario.usuario_id and dia_nro = ". $day_number." and users.id = ". $usuario_id." ORDER BY agenda_usuario_dia_horario_id ASC"), array(
        
        'day_number' => $day_number ,       
        'usuario_id' => $usuario_id 
      ));
    
   return response()->json($horario, 201);
       
 

}



public function getAgendaAtByFechaTodosTurnos(Request $request )
{
        
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));    
    $agenda_estado_id =  $request->input('agenda_estado_id');
    $usuario_id =  $request->input('usuario_id');
    $day_number = date('N', strtotime($fecha_turno));
    $day_number = $day_number;
    $horario = DB::select( DB::raw("SELECT agenda_horario.id, hora_desde, hora_hasta, hora_desde_hasta, agenda_horaria_id, agenda_dias.id as agenda_dia_id, dia_nro, dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = :fecha_turno ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id and users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null and es_habilitado = 'S' and dia_nro = :day_number 
    "), array(
        'fecha_turno' => $fecha_turno,
        'day_number' => $day_number        
      ));
    
   return response()->json($horario, 201);
       
 

}


 
// METODO QUE DEVUELVE LOS TURNOS DISPONIBLES PARA LA FECHA PARA UN MEDICOS --- TELEFONISTAS Y TUNOS NUEVOS
public function getAgendaAtByFechaMedicoTurnos(Request $request )
{
       

/*
SELECT * FROM  agenda_horario,agenda_dias,users, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = '2019-03-18' ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id  and  users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null and usuario_id = 5

SELECT * FROM  agenda_horario,agenda_dias,users,agenda_medico_bloqueo_horario, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = '2019-03-25' ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  LEFT JOIN (select * from agenda_medico_bloqueo_horario where fecha = '2019-03-27' ) AS agenda_usuario_dia_horario_bloq ON agenda_usuario_dia_horario.id = agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id  and  users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null and agenda_usuario_dia_horario.usuario_id = 5 GROUP BY agenda_horario.id


valida???
SELECT * FROM  agenda_horario,agenda_dias,users,agenda_medico_bloqueo_horario, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = '2019-03-25' ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  LEFT JOIN (select * from agenda_medico_bloqueo_horario where fecha = '2019-03-27' ) AS agenda_usuario_dia_horario_bloq ON agenda_usuario_dia_horario.id = agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id  and  users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null  and agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id is null and agenda_usuario_dia_horario.usuario_id = 5 GROUP BY agenda_horario.id
*/
    $tmp_fecha = str_replace('/', '-', $request->input('fecha_turno'));
    $fecha_turno =  date('Y-m-d H:i', strtotime($tmp_fecha));   
    $t_fecha = str_replace('/', '-', $request->input('fecha'));
    $fecha =  date('Y-m-d H:i', strtotime($t_fecha));    
    $agenda_estado_id =  $request->input('agenda_estado_id');
    $usuario_id =  $request->input('usuario_id');
    $day_number = date('N', strtotime($fecha_turno));
    $day_number = $day_number;
//echo $day_number;
if($day_number == 7){
    $day_number = 0;
}
//echo  $day_number;
//echo  $fecha_turno;
//echo $fecha;
//echo $usuario_id;
/* 
    $horario = DB::select( DB::raw("SELECT agenda_horario.id, hora_desde, hora_hasta, hora_desde_hasta, agenda_horaria_id, agenda_dias.id as agenda_dia_id,
    dia_nro, dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as
    agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado
    FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario
    LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = :fecha_turno ) as _agenda_dia_horario_atencion 
    ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id 
    where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id 
    and users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null 
    and es_habilitado = 'S' and dia_nro = :day_number and usuario_id = :usuario_id
    "), array(
        'fecha_turno' => $fecha_turno,
        'day_number' => $day_number,
        'usuario_id' => $usuario_id
      ));
  
  */
  $horario = DB::select( DB::raw("SELECT agenda_horario.id as agenda_horario_id, agenda_horario.hora_desde , agenda_horario.hora_hasta as agenda_horario_hora_hasta,
   agenda_horario.hora_desde_hasta as agenda_horario_hora_desde_hasta, agenda_horario.agenda_horaria_id as agenda_horario_agenda_horaria_id, agenda_dias.id as agenda_dia_id,
    dia_nro, dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as
    agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado
    FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario
    LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = :fecha_turno ) as _agenda_dia_horario_atencion 
    ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id 
    LEFT JOIN (select * from agenda_medico_bloqueo_horario where fecha = :fecha ) AS agenda_usuario_dia_horario_bloq 
    ON agenda_usuario_dia_horario.id = agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id
    where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id
    and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id 
    and users.id = agenda_usuario_dia_horario.usuario_id 
    and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null 
    and agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id is null
    and es_habilitado = 'S' 
    and dia_nro = :day_number 
    and agenda_usuario_dia_horario.usuario_id = :usuario_id
   
    "), array(
        'fecha_turno' => $fecha_turno,
        'day_number' => $day_number,
        'usuario_id' => $usuario_id,
        'fecha' => $fecha
      ));
    

   // $horario = DB::select( DB::raw("SELECT * FROM  agenda_horario ,agenda_dias,users,agenda_medico_bloqueo_horario, agenda_usuario_dia_horario LEFT JOIN (select * from agenda_dia_horario_atencion where fecha_turno = '2019-03-25' ) as _agenda_dia_horario_atencion ON agenda_usuario_dia_horario.id = _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id  LEFT JOIN (select * from agenda_medico_bloqueo_horario where fecha = '2019-03-27' ) AS agenda_usuario_dia_horario_bloq ON agenda_usuario_dia_horario.id = agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id where agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id  and  users.id = agenda_usuario_dia_horario.usuario_id and _agenda_dia_horario_atencion.agenda_usuario_dia_horario_id is null  and agenda_usuario_dia_horario_bloq.agenda_usuario_dia_horario_id is null and agenda_usuario_dia_horario.usuario_id = 5 GROUP BY agenda_horario.id"));
   return response()->json($horario, 201);

}

    public function getAgendaByMedicoAndDia(Request $request )
    {
       
        // FALTA UNIR LA TABLA agenda_medico_bloqueo_horario INSERTADA PARA FILTRAR
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $agenda_dia_id = $request->input('agenda_dia_id');
        $es_habilitado =  $request->input('es_habilitado');
    
        $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->select(
            'agenda_usuario_dia_horario.id',
            'agenda_usuario_dia_horario.agenda_horario_id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_dia_id',
            'usuario_id',
            'nombreyapellido',
            'dia_nombre',
            'dia_nro')
            ->where('agenda_usuario_dia_horario.es_habilitado','=',$es_habilitado)
            ->get();
           
        return $this->showAll($horario);
    
    }



    public function getAgendaByMedicoAndDiaTodoEstado(Request $request )
    {
       
        //SELECT * FROM agenda_usuario_dia_horario , agenda_dia_horario_atencion WHERE agenda_usuario_dia_horario.id != agenda_dia_horario_atencion.agenda_usuario_dia_horario_id
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $agenda_dia_id = $request->input('agenda_dia_id');
        $es_habilitado =  $request->input('es_habilitado');
        
        $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->select(
            'agenda_usuario_dia_horario.id',
            'agenda_usuario_dia_horario.agenda_horario_id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_usuario_dia_horario.es_habilitado',
            'agenda_dia_id',
            'usuario_id',
            'nombreyapellido',
            'dia_nombre',
            'dia_nro')
            ->where('agenda_usuario_dia_horario.agenda_dia_id','=',$agenda_dia_id)
            ->where('agenda_usuario_dia_horario.usuario_id','=',$usuario_id)
            ->get();
           
        return $this->showAll($horario);
    
    }

    
    public function getAgendaByMedicoAndDiaDisponible(Request $request )
    {
       
        //SELECT * FROM agenda_usuario_dia_horario , agenda_dia_horario_atencion WHERE agenda_usuario_dia_horario.id != agenda_dia_horario_atencion.agenda_usuario_dia_horario_id
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $agenda_dia_id = $request->input('agenda_dia_id');
        $es_habilitado =  $request->input('es_habilitado');
        
        $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')
        ->join('agenda_dia_horario_atencion', 'agenda_dia_horario_atencion.id', '!=', 'agenda_usuario_dia_horario.id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->select(
            'agenda_usuario_dia_horario.id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_dia_id',
            'usuario_id',
            'nombreyapellido',
            'dia_nombre',
            'dia_nro')
            ->where('agenda_usuario_dia_horario.agenda_dia_id','=',$agenda_dia_id)
            ->where('agenda_usuario_dia_horario.usuario_id','=',$usuario_id)
            ->where('agenda_usuario_dia_horario.es_habilitado','=',$es_habilitado)
            ->get();
           
        return $this->showAll($horario);
    
    }



    public function getHistoriaPaciente(Request $request,$id )
{
       
    $agenda_estado_id =  $request->input('paciente_id');
   
  $horario = DB::select( DB::raw("SELECT paciente.apellido as paciente_apellido, paciente.nombre as paciente_nombre,paciente.dni as paciente_dni,obra_social.nombre  as paciente_obra_social_nombre,
  agenda_horario.id as agenda_horario_id, agenda_horario.hora_desde , agenda_horario.hora_hasta as agenda_horario_hora_hasta,
  agenda_horario.hora_desde_hasta , agenda_horario.agenda_horaria_id as agenda_horario_agenda_horaria_id, agenda_dias.id as agenda_dia_id, es_sobreturno,
   dia_nro, agenda_dia_horario_atencion.fecha_turno as dia_nombre, users.id as usuario_id , users.nombreyapellido, agenda_usuario_dia_horario.id as
   agenda_usuario_dia_horario_id, agenda_usuario_dia_horario.es_habilitado, agenda_dia_horario_atencion.fecha_turno, agenda_estado.estado, agenda_dia_horario_atencion.llegada, agenda_dia_horario_atencion.atendido, agenda_dia_horario_atencion.es_observacion, agenda_dia_horario_atencion.operacion_cobro_id
   FROM agenda_horario,agenda_dias,users, agenda_usuario_dia_horario,agenda_dia_horario_atencion,paciente ,obra_social, agenda_estado
   WHERE
   agenda_dia_horario_atencion.agenda_usuario_dia_horario_id = agenda_usuario_dia_horario.id
   and agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id 
   and agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id
   and users.id = agenda_usuario_dia_horario.usuario_id 
   and paciente.obra_social_id = obra_social.id
   and paciente.id = agenda_dia_horario_atencion.paciente_id
   and agenda_estado.id = agenda_dia_horario_atencion.agenda_estado_id
   and agenda_dia_horario_atencion.paciente_id = :paciente_id  
ORDER BY agenda_usuario_dia_horario_id  ASC 
   
    "), array(        
        'paciente_id' => $id        
      ));
   return response()->json($horario, 201);

}


    public function getAgendaByDiaDisponible(Request $request )
    {
       
        //SELECT * FROM agenda_usuario_dia_horario , agenda_dia_horario_atencion WHERE agenda_usuario_dia_horario.id != agenda_dia_horario_atencion.agenda_usuario_dia_horario_id
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $agenda_dia_id = $request->input('agenda_dia_id');
        $es_habilitado =  $request->input('es_habilitado');
        
        $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')
        ->join('agenda_dia_horario_atencion', 'agenda_dia_horario_atencion.id', '!=', 'agenda_usuario_dia_horario.id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->select(
            'agenda_usuario_dia_horario.id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_dia_id',
            'usuario_id',
            'nombreyapellido',
            'dia_nombre',
            'dia_nro')
            ->where('agenda_usuario_dia_horario.agenda_dia_id','=',$agenda_dia_id)            
            ->where('agenda_usuario_dia_horario.es_habilitado','=',$es_habilitado)
            ->orderByRaw('agenda_horario.hora_desde ASC')
            ->orderByRaw('nombreyapellido ASC')
            ->get();
           
        return $this->showAll($horario);
    
    }



    public function getAgendaEliminados(Request $request )
    {
       
       
       
        //SELECT * FROM agenda_usuario_dia_horario , agenda_dia_horario_atencion WHERE agenda_usuario_dia_horario.id != agenda_dia_horario_atencion.agenda_usuario_dia_horario_id
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $agenda_dia_id = $request->input('agenda_dia_id');
        $es_habilitado =  $request->input('es_habilitado');
        
        $horario = DB::table('agenda_usuario_dia_horario','agenda_horario', 'users','agenda_dias','agenda_dia_horario_atencion_eliminado', 'agenda_estado','paciente','obra_social')
        ->join('users', 'users.id', '=', 'agenda_usuario_dia_horario.usuario_id')
        ->join('agenda_dia_horario_atencion_eliminado', 'agenda_dia_horario_atencion_eliminado.agenda_usuario_dia_horario_id', '=', 'agenda_usuario_dia_horario.id')
        ->join('agenda_dias', 'agenda_dias.id', '=', 'agenda_usuario_dia_horario.agenda_dia_id')
        ->join('agenda_horario', 'agenda_horario.id', '=', 'agenda_usuario_dia_horario.agenda_horario_id')
        ->join('agenda_estado', 'agenda_estado.id', '=', 'agenda_dia_horario_atencion_eliminado.agenda_estado_id')
        ->join('paciente', 'paciente.id', '=', 'agenda_dia_horario_atencion_eliminado.paciente_id')
        ->join('obra_social', 'obra_social.id', '=', 'paciente.obra_social_id')
        ->select(
            'paciente.apellido as paciente_apellido',
            'paciente.nombre as paciente_nombre',
            'paciente.dni as paciente_dni',
            'paciente.telefono_cel as telefono_cel',
            'paciente.telefono_fijo as telefono_fijo',
            'agenda_dia_horario_atencion_eliminado.llegada',
            'agenda_dia_horario_atencion_eliminado.atendido',
            'agenda_dia_horario_atencion_eliminado.es_observacion',
            'agenda_dia_horario_atencion_eliminado.operacion_cobro_id',
            'agenda_dia_horario_atencion_eliminado.fecha_turno',
            'obra_social.nombre as paciente_obra_social_nombre',
            'obra_social.id as paciente_obra_social_id',
            'agenda_estado.estado',
            'agenda_usuario_dia_horario.id',
            'agenda_horario.hora_desde',
            'agenda_horario.hora_hasta',
            'agenda_horario.hora_desde_hasta',
            'agenda_dia_id',
            'usuario_id',
            'nombreyapellido',
            'agenda_dia_horario_atencion_eliminado.fecha_turno as dia_nombre',
            'dia_nro')                                  
            ->orderByRaw('agenda_horario.hora_desde ASC')
            ->orderByRaw('nombreyapellido ASC')
            ->get();
           
        return $this->showAll($horario);
    
    }
    

    public function crearAgendaByHorario(Request $request )
    {
        
        $hora_desde = 7;
        $hora_hasta = 23;
        //$input = $request->all();
        $minutos = $request->input('minuto');
        $agenda_minutos_id= $request->input('agenda_minutos_id');
        $desde;
        $hasta;
        //ITERO POR TODAS LAS HORAS
        for($i=$hora_desde; $i<=$hora_hasta; $i++){
          //  echo $i;
          //ITERO POR EL PERIODO DE HORA
          $j=0;
          while($j<=60){
               
          //  for($j=0; $j<60; $j = $j+$minutos){
                if($i==$hora_hasta){ // si es el ultimo horario solo contempla un  solo turno
                    if($j==0){
                        $num_padded =   sprintf("%02d", $i);                      
                        $cuentaminutos = $minutos;
    
                        if($cuentaminutos == 60){
                            $cuentaminutos = 0;
                        }
                        $cuentaminutos = sprintf("%02d", $j);
                        $horario = $num_padded.":".$cuentaminutos.":00";
                        $desde = $hasta;
                        $hasta = $horario;
                        $this->confeccionarHorario($desde,$hasta,$agenda_minutos_id);
                    }
                }else{                   
                    $num_padded =   sprintf("%02d", $i);                      
                    $cuentaminutos = $minutos;

                    $cuentaminutos = sprintf("%02d", $j);
                    if($cuentaminutos == 60){
                        $cuentaminutos = 0;
                        $cuentaminutos = sprintf("%02d", $cuentaminutos);
                    }

                   
                    $horario = $num_padded.":".$cuentaminutos.":00";
                 
                 if($j!=60){ // si no llego al final agrego elementos
                    if($j==0){ // si es el primer registro lo guardo y reemplazo
                        $desde = $horario;
                        $hasta =$horario;
                    }else{
                        $desde = $hasta;
                        $hasta = $horario;
                        echo $desde;
                        echo $hasta;
                        
                         $this->confeccionarHorario($desde,$hasta,$agenda_minutos_id);                    
                    }
                }

                    if($j == 60){ // si llego al ultimo elemento lo agrego
                        
                        $desde = $hasta;                     
                        $num_padded = $num_padded+1;    
                        $num_padded = sprintf("%02d", $num_padded);
                        $hasta =  $num_padded.":".$cuentaminutos.":00";                        
                            $cuentaminutos = 0;                          
                        $this->confeccionarHorario($desde,$hasta,$agenda_minutos_id);
                    }
                 }
                 $j = $j+$minutos;
        }
    }
        return "proceso ejecutado";
    }


    

   
    public function generarHorarioAgenda(Request $request )
    {
        
        $input = $request->all();
        $usuario_id = $request->input('usuario_id');
        $dia = $request->input('dia');
        $agenda_horaria_id = $request->input('agenda_horaria_id');

        $horario = DB::table('agenda_horario')
        ->select('id')
        ->where('agenda_horaria_id','=', $agenda_horaria_id)
        ->get();    
       //  var_dump(count($t));
        $t = json_decode($horario, true);
        for($i=0 ;$i< count($t); $i++){      
             $id =    DB::table('agenda_usuario_dia_horario')->insert([
            ['agenda_horario_id' => $t[$i]["id"], 'agenda_dia_id' => $dia, 'usuario_id' => $usuario_id, 'es_habilitado' => "S"]           
            ]);           
        }
        return response()->json("Datos guardados", "200");
    }


    
    public function confeccionarHorarioDiaMedico($agenda_horario_id,$agenda_dia_id,$usuario_id, $es_habilitado){
        $horarioConfeccionado="";
        if($desde == $hasta){

        }else{
            $horarioConfeccionado = $desde." a  ".$hasta;

            $id =    DB::table('agenda_usuario_dia_horario')->insert([
                ['agenda_horario_id' => $agenda_horario_id, 'agenda_dia_id' => $agenda_dia_id, 'usuario_id' => $usuario_id, 'es_habilitado' => $es_habilitado]
               
            ]);          
        }       
    }


    public function DeshabilitarHorarioByMedico(Request $request, $id ){
        $es_habilitado = $request->input('es_habilitado');
       $res =  DB::table('agenda_usuario_dia_horario')
        ->where('id', $id)
        ->update(['es_habilitado' => $es_habilitado]);
       return $res;
        // return $this->showOne($res);
    }

    public function confeccionarHorario($desde,$hasta,$agenda_horario_id){
        $horarioConfeccionado="";
        if($desde == $hasta){

        }else{
            $horarioConfeccionado = $desde." a  ".$hasta;

            $id =    DB::table('agenda_horario')->insert([
                ['hora_desde' => $desde, 'hora_hasta' => $hasta, 'hora_desde_hasta' => $horarioConfeccionado, 'agenda_horaria_id' => $agenda_horario_id]
               
            ]);          
        }       
    }

    public function asignarTurno(Request $request){
        

        
        $id =    DB::table('agenda_dia_horario_atencion')->insertGetId([
            'agenda_usuario_dia_horario_id' => $request->agenda_usuario_dia_horario_id, 
            'paciente_id' => $request->paciente_id, 
            'usuario_alta_id' => $request->usuario_id, 
            'fecha_turno' => $request->fecha_turno,           
            'observacion' => $request->observacion,
            'es_alerta' => $request->es_alerta,
             'agenda_estado_id' => $request->agenda_estado_id,
             'es_observacion' => $request->es_observacion,
             'es_sobreturno' => $request->es_sobreturno,
             'operacion_cobro_id' => 0,
            'llegada' => "2099-12-31 00:00:00",
            'atendido' => "2099-12-31 00:00:00",
             'created_at' => date("Y-m-d H:i:s"),
             'updated_at' => date("Y-m-d H:i:s")
        ]);          
        }   




    public function getDias()
    {

        $horario = DB::table('agenda_dias')->select('dia_nro', 'dia_nombre','id')->get();
        return $this->showAll($horario);
    }

    public function getHorario()
    {

        $horario = DB::table('agenda_horario_periodo')->select('minutos', 'rango_horario','id')->get();
        return $this->showAll($horario);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Agenda  $agenda
     * @return \Illuminate\Http\Response
     */


     // OBTENGO LAS FECHAS DE BLOQUEO DE UN MEDICO
    public function getAgendaBloqueo(Request $request){
        
        //$fecha = $request->input('fecha_desde');        
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_desde'));
        $fecha =  date('Y-m-d H:i', strtotime($tmp_fecha));   
        $usuario_id = $request->input('usuario_id');
        $horario = DB::table('agenda_medico_bloqueo')->select('fecha','usuario_id','bloqueo')
        ->where('fecha','>=',$fecha)
        ->where('fecha','<=','2099-12-31')
        ->where('usuario_id','=',$usuario_id)
        ->distinct()
        ->get();
        return $this->showAll($horario);
                   
        
        return response()->json("Datos guardados", "200");
    }


    //DEVUELVE LA AGENDA DE UN MEDICO POR FECHA PARA BLOQUEAR ESA FECHA
    public function getAgendaBloqueoByMedicoAndDiaTodoEstado(Request $request )
    {
        $tmp_fecha = str_replace('/', '-', $request->input('fecha'));
    $fecha =  date('Y-m-d H:i', strtotime($tmp_fecha));   
    $usuario_id =  $request->input('usuario_id');     
    $day_number = date('N', strtotime($fecha));
    $day_number = $day_number;
    //echo $day_number;
    if($day_number == 7){
        $day_number = 0;
    }

        $horario = DB::select( DB::raw("SELECT agenda_dias.id, agenda_dias.dia_nro, agenda_dias.dia_nombre, agenda_horario.hora_desde, agenda_horario.hora_hasta, agenda_horario.hora_desde_hasta, users.nombreyapellido, agenda_usuario_dia_horario.id as agenda_usuario_dia_horario_id , agenda_usuario_dia_horario.usuario_id, agenda_usuario_dia_horario.es_habilitado ,_agenda_medico_bloqueo_horario.fecha  
        FROM  agenda_dias,agenda_horario ,users, agenda_usuario_dia_horario 
        LEFT JOIN (select * from agenda_medico_bloqueo_horario where fecha = :fecha ) as _agenda_medico_bloqueo_horario 
        ON agenda_usuario_dia_horario.id = _agenda_medico_bloqueo_horario.agenda_usuario_dia_horario_id  
        WHERE _agenda_medico_bloqueo_horario.agenda_usuario_dia_horario_id is null 
        AND agenda_dias.id = agenda_usuario_dia_horario.agenda_dia_id 
        AND agenda_horario.id = agenda_usuario_dia_horario.agenda_horario_id 
        AND users.id = agenda_usuario_dia_horario.usuario_id AND agenda_usuario_dia_horario.usuario_id = :usuario_id 
        AND agenda_dias.dia_nro = :day_number          
        
         "), array(             
             'day_number' => $day_number,
             'usuario_id' => $usuario_id,
             'fecha' => $fecha
           ));
   
           return response()->json($horario, 201);
    
    }


    // BLOQUEA AGENDA POR PERIODO DE TIEMPO
    public function bloquearAgenda(Request $request){
        $t =$request;
        //$t =$request;
    //    $someArray = json_decode($t["AgendaDiaBloqueo"]);
        $i = 0;
        while(isset($t[$i])){
            
        
     
      //  $usuario_id=$t[$i]["usuario_id"];
        $tmp_fecha = str_replace('/', '-', $t[$i]["fecha"]);
        $fecha_desde =  date('Y-m-d H:i', strtotime($tmp_fecha));   
         $id =    DB::table('agenda_medico_bloqueo')->insertGetId(
            ['fecha' => $fecha_desde,
            'bloqueo' => 'BLOQUEO',
             'usuario_id' => $t[$i]["usuario_id"]]           
            );   
            $i++;
        }
     // var_dump( $res);
     // $t = $request[0]["fecha"];
    //  echo $fecha_desde;
     //echo $t["usuario_id"]; 
  //   echo $request[1]["fecha"];

   // }
 //var_dump($someArray);
    // $q =$request.count();
    // var_dump($res)
        return response()->json($t[1]["usuario_id"], "201");
    
   // var_dump($someArray);
      //  echo $fecha_desde;
      //  echo $fecha_hasta;
       // echo $usuario_id;
    }

    public function bloquearAgendaTurno(Request $request){
        
        $agenda_usuario_dia_horario_id = $request->input('agenda_usuario_dia_horario_id');
        $fecha = $request->input('fecha');
        $usuario_id = $request->input('usuario_id');

             $id =    DB::table('agenda_medico_bloqueo_horario')->insertGetId(
            ['agenda_usuario_dia_horario_id' => $agenda_usuario_dia_horario_id, 'fecha' => $fecha, 'usuario_id' => $usuario_id]           
            );           
        
        return response()->json("Datos guardados", "200");
    }

    
    
    public function update(Request $request, $id)
    {
         
            $request['fecha_turno'] = date('Y-m-d H:i', strtotime($request['fecha_turno']));             
           $update = DB::table('agenda_dia_horario_atencion') 
            ->where('id', $id) ->limit(1) 
            ->update( [ 
             'agenda_usuario_dia_horario_id' => $request['id'],       
             'fecha_turno' => $request['fecha_turno'],
             'observacion' => $request['observacion'],
             'llegada' =>  $request['llegada']   ,             
             'atendido' =>  $request['atendido'] ,
             'agenda_estado_id' => $request['agenda_estado_id'],
            'paciente_id' => $request['paciente_id'],                  
             'updated_at' => date("Y-m-d H:i:s")     ]); 
             $agendaHorario = AgendaHorarioAtencion::findOrFail($id);
            return $this->showOne($agendaHorario);          
    }




    public function updateAgendaOperacionCobro(Request $request, $id)
    {
           //echo $request['email_laboral'];                      
           $update = DB::table('agenda_dia_horario_atencion') 
            ->where('id', $request['agenda_dia_horario_atencion_id']) ->limit(1) 
            ->update( [ 
             'operacion_cobro_id' => $id, 
             ]);                                         
             $agendaHorario = AgendaHorarioAtencion::findOrFail($id);
            return $this->showOne($agendaHorario);
            
           return response()->json($request, 200);
    }


    public function cancelarTurno( $id)
    {
        $t= DB::insert("
        INSERT INTO agenda_dia_horario_atencion_eliminado (agenda_usuario_dia_horario_id,usuario_alta_id,llegada,atendido,fecha_turno,agenda_estado_id,es_alerta,paciente_id,observacion, operacion_cobro_id, es_observacion) 
        SELECT agenda_usuario_dia_horario_id,usuario_alta_id,llegada,atendido,fecha_turno,7,es_alerta,paciente_id,observacion, operacion_cobro_id,es_observacion
        FROM agenda_dia_horario_atencion  WHERE  agenda_dia_horario_atencion.id = ".$id." ");

              DB::delete(" DELETE  FROM agenda_dia_horario_atencion   WHERE id= ".$id.""); 
         return response()->json('OK', 201);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Agenda  $agenda
     * @return \Illuminate\Http\Response
     */
    public function destroy(Agenda $agenda)
    {
        //
    }
}
