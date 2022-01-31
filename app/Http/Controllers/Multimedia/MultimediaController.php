<?php

namespace App\Http\Controllers\Multimedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class MultimediaController extends ApiController
{



public function getMultimedia(Request $request){

    
    $tipo_noticias = $request->input('tipo_noticias');

    if($tipo_noticias === 'privado'){
        $res = DB::select( DB::raw(
            " SELECT `id`, `titulo`, `descripcion`, `pagina`, `fecha_creacion`, `tiene_enlace`, `enlace`, `tiene_imagen`, `imagen`, `es_youtube`, `enlace_video_youtube`, `es_video`, `enlace_video`, `es_importante`, `estado`, `es_curso`, `created_at`, `updated_at` FROM `informa_privado` ORDER BY  fecha_creacion  DESC
           "));
         
    } if($tipo_noticias === 'publico'){
        $res = DB::select( DB::raw(
            " SELECT `id`, `titulo`, `descripcion`, `pagina`, `fecha_creacion`, `tiene_enlace`, `enlace`, `tiene_imagen`, `imagen`, `es_youtube`, `enlace_video_youtube`, `es_video`, `enlace_video`, `es_importante`, `estado`, `created_at`, `updated_at` FROM `informa_publico` ORDER BY  fecha_creacion  DESC
           "));
         
    }
  
     return $res;
}


public function delMultimediaPrivado(Request $request)
{
    $id = $request->id;

DB::table('informa_privado')->where('id', '=', $id)->delete();
return response()->json($id, "200");
}

public function delMultimediaPublico(Request $request)
{
    $id = $request->id;
DB::table('informa_publico')->where('id', '=', $id)->delete();
return response()->json($id, "200");
}





public function UploadNew(Request $request) {

    $destino =$request->input("destino");      

    //echo $destino;
    $file = $request->file('file_data');
     $fecha = date("Y-m-d-H-i-s");
     $allowedfileExtension=['pdf','jpg','png','docx','doc','PDF','pdf','xls', 'xlsx'];
     $filename = $file->getClientOriginalName();
 //    echo $filename;
     $extension = $file->getClientOriginalExtension();
 //    echo $extension;
     $check=in_array($extension,$allowedfileExtension);
     $parts = explode('/', $request->url());    
      $last = end($parts);
  //   echo $last;
     $destinationPath = 'uploads/'.$destino.'/';
 
     $without_extension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
     




        $file->move($destinationPath,$filename);


    return response()->json("Upload Successfully ", 201); 
 }


 public function UploadNewBody(Request $request){

        $ruta =  $request->ruta;    
    if($ruta=== 'privado'){
        $id =    DB::table('informa_privado')->insertGetId([              
           'descripcion' => $request->descripcion, 
           'enlace' => $request->file_data, 
           'enlace_video' => $request->enlace_video, 
           'enlace_video_youtube' => $request->enlace_video_youtube, 
           'es_curso' => $request->es_curso, 
           'es_importante' => $request->es_importante, 
           'es_video' => $request->es_video, 
           'es_youtube' => $request->es_youtube, 
           'estado' => $request->estado, 
           'fecha_creacion' => $request->fecha_creacion,            
           'imagen' => $request->file_data, 
           'pagina' => $request->pagina, 
           'tiene_enlace' => $request->tiene_enlace, 
           'tiene_imagen' => $request->tiene_imagen, 
           'titulo' => $request->titulo, 
           "created_at" => date("Y-m-d H:i:s"),
           "updated_at" => date("Y-m-d H:i:s"),         
       ]);        

    }else{
        $id =    DB::table('informa_publico')->insertGetId([              
            'descripcion' => $request->descripcion, 
            'enlace' => $request->file_data, 
            'enlace_video' => $request->enlace_video, 
            'enlace_video_youtube' => $request->enlace_video_youtube, 
            'es_curso' => $request->es_curso, 
            'es_importante' => $request->es_importante, 
            'es_video' => $request->es_video, 
            'es_youtube' => $request->es_youtube, 
            'estado' => $request->estado, 
            'fecha_creacion' => $request->fecha_creacion, 
            'imagen' => $request->file_data, 
            'pagina' => $request->pagina, 
            'tiene_enlace' => $request->tiene_enlace, 
            'tiene_imagen' => $request->tiene_imagen, 
            'titulo' => $request->titulo, 
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s"),         
        ]);   
    }



 }

}
